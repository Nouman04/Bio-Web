<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Worksheet\StoreWorksheetRequest;
use App\Jobs\GenerateWorksheetPdf;
use App\Models\Course;
use App\Models\Worksheet;
use App\Models\WorksheetPdf;
use App\Services\WorksheetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Worksheets in the student portal: a student builds their own practice paper
 * out of the question bank, then downloads it with or without the answers.
 *
 * Everything is scoped to the student — they can only draw on courses they
 * subscribe to, and only ever see worksheets they built themselves.
 */
class StudentWorksheetController extends Controller
{
    public function __construct(private readonly WorksheetService $worksheets)
    {
    }

    /**
     * The worksheets this student has built.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $course = $request->input('course');

        $worksheets = Worksheet::query()
            ->where('created_by', $request->user()->id)
            ->with(['course:id,uuid,title'])
            ->withCount('assessments')
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($course, fn ($query) => $query->whereIn('course_id', Course::where('uuid', $course)->select('id')))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('student.worksheets.index', [
            'worksheets' => $worksheets,
            'courses' => $this->worksheets->subscribedCourses($request->user()),
            'filters' => ['search' => $search, 'course' => $course],
        ]);
    }

    /**
     * The builder: pick a course, then narrow by chapter, topic, paper and year.
     */
    public function create(Request $request)
    {
        return view('student.worksheets.create', [
            'courses' => $this->worksheets->subscribedCourses($request->user()),
            'papers' => $this->worksheets->paperNumbers(),
            'years' => $this->worksheets->years(),
        ]);
    }

    /**
     * The dependent dropdowns beneath the course.
     */
    public function options(Request $request): JsonResponse
    {
        $filters = $this->filters($request);

        return response()->json([
            'chapters' => $this->worksheets->chaptersOf($filters['course'])
                ->map(fn ($chapter) => [
                    'uuid' => $chapter->uuid,
                    'title' => trim(($chapter->chapter_number ? $chapter->chapter_number . '. ' : '') . $chapter->title),
                ]),
            'topics' => $this->worksheets->topicsOf($filters['chapters'], $filters['course'])
                ->map(fn ($topic) => ['uuid' => $topic->uuid, 'title' => $topic->title]),
        ]);
    }

    /**
     * What the current selection would produce, before anything is saved.
     */
    public function preview(Request $request): JsonResponse
    {
        return response()->json(
            $this->worksheets->summarise($this->filters($request))
        );
    }

    /**
     * Records the worksheet and its questions.
     */
    public function store(StoreWorksheetRequest $request): JsonResponse
    {
        $filters = $this->filters($request);

        $this->assertSubscribed($request, $filters['course']);

        $worksheet = $this->worksheets->create(
            $request->user(),
            $filters,
            $request->validated()['title']
        );

        return response()->json([
            'message' => 'Worksheet created.',
            'uuid' => $worksheet->uuid,
            'questions' => $worksheet->assessments()->count(),
            'url' => route('student.worksheets.show', $worksheet->uuid),
        ], 201);
    }

    /**
     * One worksheet, with the two download buttons.
     */
    public function show(Request $request, string $worksheet)
    {
        // Twelve questions a page, like the other student lists; the summary
        // still covers the whole worksheet, and the PDFs print every question.
        $paper = $this->worksheets->paperFor($this->find($request, $worksheet), Paginator::resolveCurrentPage());
        $paper['questions']->withQueryString();

        return view('student.worksheets.show', $paper);
    }

    /**
     * The question paper: the summary page, then every question.
     */
    public function paper(Request $request, string $worksheet)
    {
        return $this->render($request, $worksheet, 'paper');
    }

    /**
     * The mark scheme: the same questions with their answers, marks, date and
     * question number.
     */
    public function markScheme(Request $request, string $worksheet)
    {
        return $this->render($request, $worksheet, 'mark-scheme');
    }

    /**
     * Starts building the question paper or mark scheme on the queue, or
     * returns the build already under way. The page then polls pdfStatus().
     */
    public function generatePdf(Request $request, string $worksheet, string $type): JsonResponse
    {
        abort_unless(in_array($type, WorksheetPdf::TYPES, true), 404);
        $record = $this->find($request, $worksheet);

        $pdf = WorksheetPdf::where('worksheet_id', $record->id)
            ->where('type', $type)
            ->whereIn('status', ['queued', 'running'])
            ->latest('id')
            ->first();

        if (! $pdf) {
            $pdf = WorksheetPdf::create([
                'worksheet_id' => $record->id,
                'type' => $type,
                'status' => 'queued',
                'progress' => 0,
                'stage' => 'Waiting to start',
            ]);
            GenerateWorksheetPdf::dispatch($pdf->id);
        }

        return response()->json($this->pdfState($pdf));
    }

    /**
     * How far a build has got, and the download link once it is done.
     */
    public function pdfStatus(Request $request, string $pdf): JsonResponse
    {
        return response()->json($this->pdfState($this->findPdf($request, $pdf)));
    }

    /**
     * The finished file.
     */
    public function downloadPdf(Request $request, string $pdf)
    {
        $record = $this->findPdf($request, $pdf);
        abort_unless($record->status === 'completed' && $record->path && Storage::disk('local')->exists($record->path), 404);

        $name = Str::slug($record->worksheet->title ?: 'worksheet')
            . '-' . ($record->type === 'mark-scheme' ? 'mark-scheme' : 'question-paper') . '.pdf';

        return Storage::disk('local')->download($record->path, $name);
    }

    public function destroy(Request $request, string $worksheet): JsonResponse
    {
        $record = $this->find($request, $worksheet);

        // The questions belong to the worksheet, not the other way round.
        $record->assessments()->delete();
        $record->worksheetChapters()->delete();
        $record->delete();

        return response()->json([
            'message' => 'Worksheet deleted.',
            'url' => route('student.worksheets'),
        ]);
    }

    /**
     * One of the two documents as HTML (`?preview=1`), which is how the page
     * is checked without waiting on wkhtmltopdf. The PDF itself is built on
     * the queue (generatePdf), so without `preview` this goes back to the
     * worksheet page, where the buttons start it.
     */
    private function render(Request $request, string $worksheet, string $which)
    {
        $record = $this->find($request, $worksheet);

        if (! $request->boolean('preview')) {
            return redirect()->route('student.worksheets.show', $record->uuid);
        }

        return view('worksheets.pdf.' . $which, $this->worksheets->paperFor($record) + ['scheme' => $which === 'mark-scheme']);
    }

    /**
     * What the page needs to draw a build's progress.
     *
     * @return array<string, mixed>
     */
    private function pdfState(WorksheetPdf $pdf): array
    {
        return [
            'id' => $pdf->uuid,
            'type' => $pdf->type,
            'status' => $pdf->status,
            'progress' => $pdf->progress,
            'stage' => $pdf->stage,
            'error' => $pdf->status === 'failed' ? 'The PDF could not be created. Please try again.' : null,
            'status_url' => route('student.worksheets.pdf.status', $pdf->uuid),
            'download_url' => $pdf->status === 'completed' ? route('student.worksheets.pdf.download', $pdf->uuid) : null,
        ];
    }

    /**
     * A build of one of this student's own worksheets.
     */
    private function findPdf(Request $request, string $uuid): WorksheetPdf
    {
        return WorksheetPdf::where('uuid', $uuid)
            ->whereHas('worksheet', fn ($q) => $q->where('created_by', $request->user()->id))
            ->with('worksheet')
            ->firstOrFail();
    }

    /**
     * One of this student's own worksheets. Somebody else's is a 404 rather
     * than a 403 — it is not theirs to know about.
     */
    private function find(Request $request, string $uuid): Worksheet
    {
        return Worksheet::where('uuid', $uuid)
            ->where('created_by', $request->user()->id)
            ->firstOrFail();
    }

    /**
     * A student may only build from a course they actually subscribe to.
     */
    private function assertSubscribed(Request $request, ?string $courseUuid): void
    {
        abort_unless(
            $this->worksheets->subscribedCourses($request->user())->contains('uuid', $courseUuid),
            403,
            'You are not subscribed to that course.'
        );
    }

    /**
     * The five things the builder narrows by.
     *
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        return [
            'course' => $request->input('course'),
            'chapters' => (array) $request->input('chapters', []),
            'topics' => (array) $request->input('topics', []),
            'papers' => (array) $request->input('papers', []),
            'years' => (array) $request->input('years', []),
        ];
    }
}

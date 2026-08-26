<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Worksheet\StoreWorksheetRequest;
use App\Models\Course;
use App\Models\Worksheet;
use App\Services\WorksheetService;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        return view('student.worksheets.show', $this->worksheets->paperFor($this->find($request, $worksheet)));
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
     * Renders one of the two documents.
     *
     * `?preview=1` returns the HTML instead of the PDF, which is how the page
     * is checked without waiting on wkhtmltopdf.
     */
    private function render(Request $request, string $worksheet, string $which)
    {
        $record = $this->find($request, $worksheet);
        $data = $this->worksheets->paperFor($record) + ['scheme' => $which === 'mark-scheme'];

        $view = 'worksheets.pdf.' . $which;

        if ($request->boolean('preview')) {
            return view($view, $data);
        }

        $name = Str::slug($record->title ?: 'worksheet')
            . '-' . ($which === 'mark-scheme' ? 'mark-scheme' : 'question-paper') . '.pdf';

        return SnappyPdf::loadView($view, $data)->download($name);
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

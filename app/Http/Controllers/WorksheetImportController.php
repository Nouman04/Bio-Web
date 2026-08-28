<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWorksheetImport;
use App\Models\Course;
use App\Models\WorksheetImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Uploading a past-paper question worksheet, and watching it import.
 *
 * The upload only stages the file and queues the work — a real worksheet runs
 * to thousands of rows, which is far longer than a request should live.
 */
class WorksheetImportController extends Controller
{
    /**
     * The columns the importer reads, in the order the template writes them.
     */
    private const COLUMNS = [
        'Question', 'Year', 'Session', 'Chapter', 'Topic',
        'Paper No.', 'Q No.', 'Marks', 'Source PDF',
    ];

    /**
     * Stages the upload and queues the import.
     */
    public function store(Request $request, Course $course): JsonResponse
    {
        $request->validate([
            // 30MB covers a 15,000-row workbook with room to spare.
            'worksheet' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:30720'],
        ], [
            'worksheet.mimes' => 'The worksheet must be an .xlsx, .xls or .csv file.',
        ]);

        $file = $request->file('worksheet');
        $path = $file->store('worksheet-imports', 'local');

        $import = WorksheetImport::create([
            'user_id' => $request->user()->id,
            'course_id' => $course->id,
            // A worksheet names its own chapters, so there is no fallback here;
            // a row without one is reported rather than guessed at.
            'chapter_id' => null,
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'status' => 'queued',
        ]);

        ProcessWorksheetImport::dispatch($import->id);

        return response()->json([
            'message' => 'Worksheet queued. Importing will continue in the background.',
            'import' => $this->state($import),
        ], 202);
    }

    /**
     * Where one import has got to, for the page to poll.
     */
    public function status(Course $course, WorksheetImport $import): JsonResponse
    {
        abort_if($import->course_id !== $course->id, 404);

        return response()->json($this->state($import));
    }

    /**
     * The most recent imports for this course, so the page can pick up a run
     * that is still going after a refresh.
     */
    public function latest(Course $course): JsonResponse
    {
        $imports = WorksheetImport::where('course_id', $course->id)
            ->latest('id')
            ->limit(5)
            ->get();

        return response()->json($imports->map(fn (WorksheetImport $import) => $this->state($import)));
    }

    /**
     * A blank workbook with the right headings, so nobody has to guess the
     * column names. Written as CSV, which Excel opens natively.
     */
    public function template(): StreamedResponse
    {
        $example = [
            'State three ways in which a red blood cell is different to a white blood cell.',
            '2018', 'March', '9 Transport in animals', '9.4 Blood', '32', '2(b)', '3', '0610_m18_qp_32.pdf',
        ];

        return response()->streamDownload(function () use ($example) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::COLUMNS);
            fputcsv($handle, $example);
            fclose($handle);
        }, 'question-worksheet-template.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array<string, mixed>
     */
    private function state(WorksheetImport $import): array
    {
        return [
            'uuid' => $import->uuid,
            'filename' => $import->filename,
            'status' => $import->status,
            'percent' => $import->percent,
            'imported' => $import->imported_rows,
            'skipped' => $import->skipped_rows,
            'created_topics' => $import->created_topics,
            'created_chapters' => $import->created_chapters,
            'failures' => $import->failures ?? [],
            'error' => $import->error,
            'finished' => $import->isFinished(),
            'started_at' => $import->started_at?->diffForHumans(),
        ];
    }
}

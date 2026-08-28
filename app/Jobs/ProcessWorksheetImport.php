<?php

namespace App\Jobs;

use App\Imports\QuestionWorksheetImport;
use App\Models\WorksheetImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

/**
 * Runs one worksheet import off the request.
 *
 * A 15,000-row file takes minutes, which is far too long to hold a browser on,
 * so the upload only records the file and this does the work. Progress lands on
 * the WorksheetImport row as each chunk finishes.
 */
class ProcessWorksheetImport implements ShouldQueue
{
    use Queueable;

    /**
     * Long enough for a large file; the chunked reader keeps memory flat, but
     * the row count is what takes the time.
     */
    public int $timeout = 1800;

    /**
     * Re-running a half-finished import would double-count rows, so a failure
     * is reported rather than retried.
     */
    public int $tries = 1;

    public function __construct(public readonly int $importId)
    {
    }

    public function handle(): void
    {
        $import = WorksheetImport::find($this->importId);

        if (! $import) {
            return;
        }

        $import->forceFill([
            'status' => 'running',
            'started_at' => now(),
            // Counted first so the page can show a real percentage rather than
            // a bar that only fills in at the end.
            'total_rows' => $this->countRows($import->path),
        ])->save();

        try {
            Excel::import(new QuestionWorksheetImport($import), $import->path, 'local');

            $import->forceFill([
                'status' => 'completed',
                'finished_at' => now(),
            ])->save();
        } catch (Throwable $e) {
            report($e);

            $import->forceFill([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'finished_at' => now(),
            ])->save();

            return;
        } finally {
            // The upload was only ever a staging copy.
            Storage::disk('local')->delete($import->path);
        }
    }

    /**
     * How many data rows the file holds, counted by streaming it rather than
     * loading it — a 15,000-row workbook must not be held in memory twice.
     *
     * Returns 0 if the file cannot be read that way; the import still runs, the
     * progress bar just has nothing to divide by.
     */
    private function countRows(string $path): int
    {
        try {
            $reader = new \OpenSpout\Reader\XLSX\Reader();
            $full = Storage::disk('local')->path($path);

            if (! str_ends_with(strtolower($full), '.xlsx')) {
                return max(0, count(file($full)) - 1);
            }

            $reader->open($full);
            $rows = 0;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $ignored) {
                    $rows++;
                }
                break;
            }

            $reader->close();

            // Less the heading row.
            return max(0, $rows - 1);
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * Reached when the job itself dies — a timeout, or the worker being killed.
     */
    public function failed(Throwable $e): void
    {
        WorksheetImport::where('id', $this->importId)->update([
            'status' => 'failed',
            'error' => $e->getMessage(),
            'finished_at' => now(),
        ]);
    }
}

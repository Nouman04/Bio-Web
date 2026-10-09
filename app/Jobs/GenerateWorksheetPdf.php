<?php

namespace App\Jobs;

use App\Models\WorksheetPdf;
use App\Services\WorksheetService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Builds a worksheet's question paper or mark scheme off the request.
 *
 * A worksheet can hold thousands of questions; rendering that inside a page
 * load runs into PHP's and wkhtmltopdf's time limits. Here neither applies,
 * and progress lands on the WorksheetPdf row so the page can show it:
 *
 *   collecting questions -> laying out the pages -> wkhtmltopdf loading the
 *   page (its own %) -> printing ("Page 12 of 300") -> done.
 */
class GenerateWorksheetPdf implements ShouldQueue
{
    use Queueable;

    /** Long enough for the largest worksheet. */
    public int $timeout = 1800;

    /** A half-written file is not worth a retry; the student can ask again. */
    public int $tries = 1;

    public function __construct(public readonly int $pdfId)
    {
    }

    public function handle(WorksheetService $worksheets): void
    {
        $pdf = WorksheetPdf::with('worksheet')->find($this->pdfId);

        if (! $pdf || ! $pdf->worksheet) {
            return;
        }

        $pdf->forceFill(['status' => 'running', 'started_at' => now(), 'error' => null])->save();
        $html = Storage::disk('local')->path('tmp/worksheet-pdf-' . $pdf->uuid . '.html');
        $path = 'worksheet-pdfs/' . $pdf->worksheet->uuid . '/' . $pdf->type . '-' . $pdf->uuid . '.pdf';

        try {
            $pdf->step(5, 'Collecting questions');
            $data = $worksheets->paperFor($pdf->worksheet) + ['scheme' => $pdf->type === 'mark-scheme'];

            $pdf->step(20, 'Laying out the pages');
            Storage::disk('local')->put('tmp/worksheet-pdf-' . $pdf->uuid . '.html', view('worksheets.pdf.' . $pdf->type, $data)->render());

            $pdf->step(25, 'Creating the PDF');
            Storage::disk('local')->makeDirectory(dirname($path));
            $this->render($pdf, $html, Storage::disk('local')->path($path));

            $pdf->forceFill([
                'status' => 'completed',
                'progress' => 100,
                'stage' => 'Ready',
                'path' => $path,
                'finished_at' => now(),
            ])->save();

            $this->dropOlderCopies($pdf);
        } catch (Throwable $e) {
            report($e);
            Storage::disk('local')->delete($path);
            $this->markFailed($pdf, $e->getMessage());
        } finally {
            @unlink($html);
        }
    }

    /**
     * The job died outside handle(): killed by its timeout, say.
     */
    public function failed(?Throwable $e): void
    {
        if ($pdf = WorksheetPdf::find($this->pdfId)) {
            $this->markFailed($pdf, $e?->getMessage() ?: 'The PDF could not be created.');
        }
    }

    /**
     * Runs wkhtmltopdf with the app's PDF settings (page size, margins, page
     * numbers - config/snappy.php) and reads its progress as it goes: first a
     * percentage while it loads the page, then "Page n of m" while it prints.
     */
    private function render(WorksheetPdf $pdf, string $html, string $output): void
    {
        $command = app('snappy.pdf')->getCommand($html, $output);
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(null);

        $printing = false;
        $last = 25;

        $process->run(function ($type, $chunk) use ($pdf, &$printing, &$last) {
            if (preg_match_all('/Page (\d+) of (\d+)/', $chunk, $pages, PREG_SET_ORDER)) {
                $printing = true;
                [, $page, $of] = end($pages);
                $progress = 60 + (int) floor(38 * $page / max(1, $of));
                $stage = "Printing page {$page} of {$of}";
            } elseif (! $printing && preg_match_all('/(\d+)%/', $chunk, $percents)) {
                $progress = 25 + (int) floor(35 * end($percents[1]) / 100);
                $stage = 'Creating the PDF';
            } else {
                return;
            }

            // A write every couple of percent is plenty for a progress bar.
            if ($progress >= $last + 2 || ($printing && $progress === 98)) {
                $last = $progress;
                $pdf->step($progress, $stage);
            }
        });

        // wkhtmltopdf exits non-zero on harmless warnings (a missing image);
        // what matters is whether the file came out.
        if (! is_file($output) || filesize($output) === 0) {
            throw new \RuntimeException(Str::limit(trim($process->getErrorOutput()) ?: 'wkhtmltopdf produced no file.', 500));
        }
    }

    /** Only the newest copy of each document is kept. */
    private function dropOlderCopies(WorksheetPdf $pdf): void
    {
        WorksheetPdf::where('worksheet_id', $pdf->worksheet_id)
            ->where('type', $pdf->type)
            ->where('id', '<', $pdf->id)
            ->whereIn('status', ['completed', 'failed'])
            ->get()
            ->each(function (WorksheetPdf $old) {
                if ($old->path) {
                    Storage::disk('local')->delete($old->path);
                }
                $old->delete();
            });
    }

    private function markFailed(WorksheetPdf $pdf, string $message): void
    {
        $pdf->forceFill([
            'status' => 'failed',
            'stage' => 'Failed',
            'error' => Str::limit($message, 1000),
            'finished_at' => now(),
        ])->save();
    }
}

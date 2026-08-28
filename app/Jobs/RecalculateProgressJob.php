<?php

namespace App\Jobs;

use App\Models\Course;
use App\Models\User;
use App\Services\ProgressService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

/**
 * Rebuilds one user's cached percentages for one course, off the request that
 * triggered it. Completing a module writes one row; working out what that did
 * to the chapter and the course is this job's problem.
 *
 * Ids rather than models are carried, so a job sitting in the queue when the
 * content changes still resolves against what is there when it runs.
 */
class RecalculateProgressJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $userId,
        public readonly int $courseId,
    ) {
    }

    /**
     * Finishing three videos in quick succession should recalculate once, not
     * three times over the top of itself.
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("progress:{$this->userId}:{$this->courseId}"))
                ->releaseAfter(5)
                ->expireAfter(120),
        ];
    }

    public function handle(ProgressService $progress): void
    {
        $user = User::find($this->userId);
        $course = Course::find($this->courseId);

        // Either could have been deleted between dispatch and running.
        if (! $user || ! $course) {
            return;
        }

        $progress->recalculate($user, $course);
    }
}

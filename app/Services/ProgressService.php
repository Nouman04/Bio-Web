<?php

namespace App\Services;

use App\Jobs\RecalculateProgressJob;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Quiz;
use App\Models\User;
use App\Models\UserCourseProgress;
use App\Models\UserModuleProgress;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Everything that decides whether a unit is finished, and everything that turns
 * finished units into a percentage. Controllers say what the reader did — this
 * says what that means.
 *
 * The percentages are worked out from the tables every time they are asked for.
 * user_course_progress only caches the answer for listings; it is never the
 * source of truth.
 */
class ProgressService
{
    /**
     * How much of a video counts as having watched it.
     */
    public const VIDEO_COMPLETE_PERCENT = 90;

    /**
     * How long a note, diagram, guide or summary must be open before it counts
     * as read. Long enough that scrolling past does not tick it off, short
     * enough not to punish a quick re-read.
     */
    public const MIN_VIEW_SECONDS = 12;

    /* ── Completion criteria ────────────────────────────────────────────── */

    /**
     * A video lesson, reported as a percentage watched. Anything at or above
     * VIDEO_COMPLETE_PERCENT finishes it; below that the position is kept so
     * the bar is still right when they come back.
     */
    public function watched(User $user, CourseModule $module, int $percent): UserModuleProgress
    {
        $percent = max(0, min(100, $percent));

        if ($percent >= self::VIDEO_COMPLETE_PERCENT) {
            return $this->complete($user, $module, 'watched', $percent);
        }

        return $this->record($user, $module, ['progress' => $percent]);
    }

    /**
     * A quiz, from what was scored on it. Passing finishes it; the score is
     * kept either way so a near miss still shows.
     *
     * A quiz with no passing score set is finished by attempting it at all —
     * there is no bar to clear.
     */
    public function attempted(User $user, Quiz $quiz, float $earned, float $total): ?UserModuleProgress
    {
        $module = $this->moduleFor($quiz);

        if (! $module) {
            return null;
        }

        $percent = $total > 0 ? (int) round($earned / $total * 100) : 0;
        $passMark = $quiz->passing_score !== null ? (float) $quiz->passing_score : null;
        $passed = $passMark === null || $earned >= $passMark;

        if ($passed) {
            return $this->complete($user, $module, 'passed', $percent);
        }

        // A failed attempt never undoes a pass already earned.
        return $this->record($user, $module, ['progress' => $percent]);
    }

    /**
     * A note, diagram, guide or summary, from how long it was open. Below the
     * threshold nothing is written — the page was not read.
     */
    public function viewed(User $user, CourseModule $module, int $seconds): ?UserModuleProgress
    {
        if ($seconds < self::MIN_VIEW_SECONDS) {
            return null;
        }

        return $this->complete($user, $module, 'viewed', 100);
    }

    /**
     * The "Mark as complete" checkbox, which works on any type and can be
     * unticked again.
     */
    public function setManual(User $user, CourseModule $module, bool $completed): UserModuleProgress
    {
        return $completed
            ? $this->complete($user, $module, 'manual', 100)
            : $this->uncomplete($user, $module);
    }

    /* ── Writing ────────────────────────────────────────────────────────── */

    /**
     * Marks a module finished. Completing something already finished leaves the
     * original timestamp alone, so "when did I first finish this" survives.
     */
    public function complete(User $user, CourseModule $module, string $via, int $progress = 100): UserModuleProgress
    {
        $existing = $this->recorded($user, $module);

        $row = $this->record($user, $module, [
            'is_completed' => true,
            'progress' => max($progress, $existing?->progress ?? 0),
            'completed_via' => $via,
            'completed_at' => $existing?->completed_at ?? Carbon::now(),
        ]);

        $this->queueRecalculation($user, $module);

        return $row;
    }

    /**
     * Undoes a completion, for the manual checkbox. The row stays, so partial
     * progress on it is not lost.
     */
    public function uncomplete(User $user, CourseModule $module): UserModuleProgress
    {
        $row = $this->record($user, $module, [
            'is_completed' => false,
            'completed_via' => null,
            'completed_at' => null,
        ]);

        $this->queueRecalculation($user, $module);

        return $row;
    }

    /**
     * Upserts the user's row for a module without deciding anything.
     */
    private function record(User $user, CourseModule $module, array $attributes): UserModuleProgress
    {
        return UserModuleProgress::updateOrCreate(
            ['user_id' => $user->id, 'course_module_id' => $module->id],
            $attributes
        );
    }

    private function recorded(User $user, CourseModule $module): ?UserModuleProgress
    {
        return UserModuleProgress::where('user_id', $user->id)
            ->where('course_module_id', $module->id)
            ->first();
    }

    /* ── Formulas ───────────────────────────────────────────────────────── */

    /**
     * One chapter's percentage:
     *
     *     completed weight in the chapter / total weight in the chapter × 100
     *
     * @return array{progress:float, completed_weight:int, total_weight:int}
     */
    public function chapterProgress(User $user, Chapter $chapter): array
    {
        return $this->weights($user, [$chapter->id])[$chapter->id] ?? $this->emptyTotals();
    }

    /**
     * The whole course's percentage, over every module in every chapter:
     *
     *     completed weight in the course / total weight in the course × 100
     *
     * Worked out from the modules directly rather than by averaging the chapter
     * percentages — a chapter with forty modules should not count the same as
     * one with two.
     *
     * @return array{progress:float, completed_weight:int, total_weight:int}
     */
    public function courseProgress(User $user, Course $course): array
    {
        $totals = $this->weights($user, $this->chapterIds($course));

        return $this->totals(
            (int) array_sum(array_column($totals, 'completed_weight')),
            (int) array_sum(array_column($totals, 'total_weight'))
        );
    }

    /**
     * Progress for a whole list of courses at once, keyed by course id. A grid
     * of course cards is one query this way rather than one per card.
     *
     * @param  iterable<int, Course>  $courses
     * @return array<int, array{progress:float, completed_weight:int, total_weight:int}>
     */
    public function courseProgressFor(User $user, iterable $courses): array
    {
        $courseIds = collect($courses)->pluck('id')->all();

        if ($courseIds === []) {
            return [];
        }

        $rows = DB::table('course_modules as m')
            ->join('chapters as c', 'c.id', '=', 'm.chapter_id')
            ->leftJoin('user_module_progress as p', function ($join) use ($user) {
                $join->on('p.course_module_id', '=', 'm.id')
                    ->where('p.user_id', '=', $user->id);
            })
            ->whereIn('c.course_id', $courseIds)
            ->where('m.is_active', true)
            ->whereNull('m.deleted_at')
            ->whereNull('c.deleted_at')
            ->groupBy('c.course_id')
            ->selectRaw('c.course_id as course_id')
            ->selectRaw('SUM(m.weight) as total_weight')
            ->selectRaw('SUM(CASE WHEN p.is_completed = 1 THEN m.weight ELSE 0 END) as completed_weight')
            ->get();

        $totals = [];

        foreach ($rows as $row) {
            $totals[(int) $row->course_id] = $this->totals(
                (int) $row->completed_weight,
                (int) $row->total_weight
            );
        }

        // A course with nothing tracked yet still belongs in the list, at zero.
        foreach ($courseIds as $id) {
            $totals[$id] ??= $this->emptyTotals();
        }

        return $totals;
    }

    /**
     * Every chapter of a course, keyed by chapter id — one query rather than
     * one per chapter.
     *
     * @return array<int, array{progress:float, completed_weight:int, total_weight:int}>
     */
    public function chapterBreakdown(User $user, Course $course): array
    {
        $ids = $this->chapterIds($course);
        $totals = $this->weights($user, $ids);

        // Chapters with no modules yet still belong in the list, at zero.
        foreach ($ids as $id) {
            $totals[$id] ??= $this->emptyTotals();
        }

        return $totals;
    }

    /**
     * The aggregate behind every percentage: total weight against the weight
     * this user has completed, grouped by chapter.
     *
     * @param  array<int, int>  $chapterIds
     * @return array<int, array{progress:float, completed_weight:int, total_weight:int}>
     */
    private function weights(User $user, array $chapterIds): array
    {
        if ($chapterIds === []) {
            return [];
        }

        $rows = DB::table('course_modules as m')
            ->leftJoin('user_module_progress as p', function ($join) use ($user) {
                $join->on('p.course_module_id', '=', 'm.id')
                    ->where('p.user_id', '=', $user->id);
            })
            ->whereIn('m.chapter_id', $chapterIds)
            ->where('m.is_active', true)
            ->whereNull('m.deleted_at')
            ->groupBy('m.chapter_id')
            ->selectRaw('m.chapter_id as chapter_id')
            ->selectRaw('SUM(m.weight) as total_weight')
            ->selectRaw('SUM(CASE WHEN p.is_completed = 1 THEN m.weight ELSE 0 END) as completed_weight')
            ->get();

        $totals = [];

        foreach ($rows as $row) {
            $totals[(int) $row->chapter_id] = $this->totals(
                (int) $row->completed_weight,
                (int) $row->total_weight
            );
        }

        return $totals;
    }

    /**
     * @return array{progress:float, completed_weight:int, total_weight:int}
     */
    private function totals(int $completed, int $total): array
    {
        return [
            // A course with no modules is 0% done, not undefined.
            'progress' => $total > 0 ? round($completed / $total * 100, 2) : 0.0,
            'completed_weight' => $completed,
            'total_weight' => $total,
        ];
    }

    /**
     * @return array{progress:float, completed_weight:int, total_weight:int}
     */
    private function emptyTotals(): array
    {
        return ['progress' => 0.0, 'completed_weight' => 0, 'total_weight' => 0];
    }

    /* ── Cache ──────────────────────────────────────────────────────────── */

    /**
     * Rebuilds this user's cached rows for a course: one per chapter, plus the
     * course total. Safe to run at any time — it only ever restates what the
     * progress rows already say.
     */
    public function recalculate(User $user, Course $course): void
    {
        $breakdown = $this->chapterBreakdown($user, $course);

        foreach ($breakdown as $chapterId => $totals) {
            $this->cache($user, $course->id, $chapterId, $totals);
        }

        $this->cache($user, $course->id, null, $this->totals(
            (int) array_sum(array_column($breakdown, 'completed_weight')),
            (int) array_sum(array_column($breakdown, 'total_weight'))
        ));
    }

    private function cache(User $user, int $courseId, ?int $chapterId, array $totals): void
    {
        UserCourseProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $courseId,
                // 0 stands for the course total; MySQL would let duplicate
                // NULLs through the unique index.
                'chapter_key' => $chapterId ?? 0,
            ],
            $totals + [
                'chapter_id' => $chapterId,
                'recalculated_at' => Carbon::now(),
            ]
        );
    }

    /**
     * The cached percentage for a course, falling back to working it out when
     * nothing has been cached yet.
     */
    public function cachedCourseProgress(User $user, Course $course): float
    {
        $row = UserCourseProgress::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('chapter_key', 0)
            ->first();

        return $row
            ? (float) $row->progress
            : $this->courseProgress($user, $course)['progress'];
    }

    /* ── Plumbing ───────────────────────────────────────────────────────── */

    /**
     * Where a user stands on a list of records, keyed by record id — one query
     * for a whole listing rather than one per card.
     *
     * Every record must be of the same type, which is what a listing gives.
     * Anything not registered as a module is simply absent from the result.
     *
     * @param  iterable<int, object>  $records
     * @return array<int, array{completed:bool, progress:int}>
     */
    public function completionFor(User $user, iterable $records): array
    {
        $records = collect($records);

        if ($records->isEmpty()) {
            return [];
        }

        $rows = DB::table('course_modules as m')
            ->leftJoin('user_module_progress as p', function ($join) use ($user) {
                $join->on('p.course_module_id', '=', 'm.id')
                    ->where('p.user_id', '=', $user->id);
            })
            ->where('m.moduleable_type', $records->first()::class)
            ->whereIn('m.moduleable_id', $records->pluck('id')->all())
            ->whereNull('m.deleted_at')
            ->selectRaw('m.moduleable_id as record_id')
            ->selectRaw('COALESCE(p.is_completed, 0) as completed')
            ->selectRaw('COALESCE(p.progress, 0) as progress')
            ->get();

        $state = [];

        foreach ($rows as $row) {
            $state[(int) $row->record_id] = [
                'completed' => (bool) $row->completed,
                'progress' => (int) $row->progress,
            ];
        }

        return $state;
    }

    /**
     * The module registered for a piece of content, if it has one.
     */
    public function moduleFor(object $record): ?CourseModule
    {
        return CourseModule::where('moduleable_type', $record::class)
            ->where('moduleable_id', $record->id)
            ->first();
    }

    /**
     * @return array<int, int>
     */
    private function chapterIds(Course $course): array
    {
        return $course->chapters()->pluck('id')->all();
    }

    /**
     * Recalculating touches every chapter of a course, so it is done off the
     * request rather than in it.
     */
    private function queueRecalculation(User $user, CourseModule $module): void
    {
        $courseId = $module->chapter?->course_id;

        if ($courseId) {
            RecalculateProgressJob::dispatch($user->id, $courseId);
        }
    }
}

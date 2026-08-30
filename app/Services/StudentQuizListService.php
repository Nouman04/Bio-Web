<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizUserAttempt;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The student's own view of the quizzes set for them.
 *
 * One row per quiz rather than per attempt: sitting a paper three times is one
 * line showing where it stands now, which is what "based on the last attempt"
 * in the filters means.
 *
 * A quiz that has never been sat is a row too — it is the one the student most
 * needs to see — so the listing is built from the quizzes themselves with the
 * latest attempt hung off each, rather than from the attempts.
 */
class StudentQuizListService
{
    /**
     * A paper nobody is still waiting on, whatever the outcome — marked
     * automatically, marked by an instructor, or handed back for the student
     * to judge themselves.
     */
    public const MARKED = ['submitted', 'graded', 'self_marked'];

    /**
     * The filters offered above the list, in the words the student reads. The
     * first two are quizzes still to deal with; the last two are history.
     */
    public const STATUSES = [
        'not_started' => 'Not started',
        'in_progress' => 'In progress',
        'pending' => 'Awaiting marking',
        'marked' => 'Marked',
    ];

    public const RESULTS = [
        'passed' => 'Passed',
        'failed' => 'Not passed',
    ];

    /**
     * Every quiz set for this student, with their latest attempt at it.
     *
     * Outstanding quizzes come first — never started, then still open — because
     * this is the student's list of what to do; everything already handed in
     * follows, most recent first.
     *
     * @param  array{course?:string|null, chapter?:string|null, status?:string|null, result?:string|null}  $filters
     */
    public function listing(User $student, array $filters = []): LengthAwarePaginator
    {
        $courseIds = $this->courses($student)->pluck('id');

        // Subscribed to nothing, so nothing is set for them.
        if ($courseIds->isEmpty()) {
            return Quiz::query()->whereRaw('1 = 0')->paginate(10)->withQueryString();
        }

        // The newest attempt per quiz. Every state a row can be in is read off
        // it, so both the ordering and the status filters go through it.
        $latest = QuizUserAttempt::query()
            ->where('user_id', $student->id)
            ->selectRaw('MAX(id)')
            ->groupBy('quiz_id');

        $quizzes = Quiz::query()
            ->select('quizzes.*')
            // Only what a student could actually sit; a draft is not set yet.
            ->whereStatus('published')
            ->whereHas('chapters', function (Builder $chapter) use ($courseIds, $filters) {
                $chapter->whereIn('chapters.course_id', $courseIds);

                if ($course = ($filters['course'] ?? null)) {
                    $chapter->whereRelation('course', 'courses.uuid', $course);
                }

                if ($chosen = ($filters['chapter'] ?? null)) {
                    $chapter->where('chapters.uuid', $chosen);
                }
            })
            ->with([
                'chapters.course',
                // This student's attempts, newest first — the first of them is
                // the one the row speaks for.
                'userAttempts' => fn ($q) => $q->where('user_id', $student->id)->latest('id'),
            ])
            ->withCount(['userAttempts as attempts_count' => fn ($q) => $q->where('user_id', $student->id)])
            // Ordering needs the latest attempt as a value, not a relation.
            ->addSelect(['latest_attempt_id' => QuizUserAttempt::query()
                ->select('id')
                ->whereColumn('quiz_id', 'quizzes.id')
                ->where('user_id', $student->id)
                ->orderByDesc('id')
                ->limit(1)]);

        $this->applyStatus($quizzes, $student, $latest, $filters['status'] ?? null);
        $this->applyResult($quizzes, $latest, $filters['result'] ?? null);

        return $quizzes
            // Never sat first, then everything else by most recent attempt.
            ->orderByRaw('CASE WHEN latest_attempt_id IS NULL THEN 0 ELSE 1 END')
            ->orderByDesc('latest_attempt_id')
            ->orderByDesc('quizzes.id')
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * How many of these quizzes the student has not sat yet, for the line above
     * the list. Counted over everything set for them, not just this page.
     */
    public function outstanding(User $student): int
    {
        $courseIds = $this->courses($student)->pluck('id');

        if ($courseIds->isEmpty()) {
            return 0;
        }

        return Quiz::query()
            ->whereStatus('published')
            ->whereHas('chapters', fn (Builder $chapter) => $chapter->whereIn('chapters.course_id', $courseIds))
            ->whereDoesntHave('userAttempts', fn ($q) => $q->where('user_id', $student->id))
            ->count();
    }

    /**
     * Narrows to where the latest attempt stands — or to quizzes with no
     * attempt at all, which is what "not started" means.
     */
    private function applyStatus(Builder $quizzes, User $student, $latest, ?string $status): void
    {
        if ($status === 'not_started') {
            $quizzes->whereDoesntHave('userAttempts', fn ($q) => $q->where('user_id', $student->id));

            return;
        }

        $states = match ($status) {
            'in_progress' => ['in_progress'],
            'pending' => ['pending_review'],
            'marked' => self::MARKED,
            default => null,
        };

        if ($states === null) {
            return;
        }

        $quizzes->whereHas(
            'userAttempts',
            fn ($q) => $q->whereIn('id', $latest)->whereStatus($states)
        );
    }

    /**
     * Pass and fail are only meaningful once a paper has been marked; an
     * unmarked one is neither, rather than quietly counting as a fail.
     */
    private function applyResult(Builder $quizzes, $latest, ?string $result): void
    {
        if (! in_array($result, ['passed', 'failed'], true)) {
            return;
        }

        $quizzes->whereHas('userAttempts', fn ($q) => $q
            ->whereIn('id', $latest)
            ->whereStatus(self::MARKED)
            ->where('passed', $result === 'passed'));
    }

    /**
     * The courses the student subscribes to, for the course filter.
     *
     * @return Collection<int, Course>
     */
    public function courses(User $student): Collection
    {
        $uuids = $student->subscriptions
            ->filter(fn ($subscription) => $subscription->valid())
            ->map(fn ($subscription) => Str::after($subscription->type, 'course_'))
            ->filter()
            ->unique();

        if ($uuids->isEmpty()) {
            return collect();
        }

        return Course::whereIn('uuid', $uuids)->orderBy('title')->get(['id', 'uuid', 'title']);
    }

    /**
     * The chapters offered by the chapter filter — narrowed to the chosen
     * course when there is one, so the two dropdowns agree.
     *
     * @return Collection<int, Chapter>
     */
    public function chapters(User $student, ?string $course = null): Collection
    {
        $courses = $this->courses($student);

        if ($course) {
            $courses = $courses->where('uuid', $course);
        }

        if ($courses->isEmpty()) {
            return collect();
        }

        return Chapter::whereIn('course_id', $courses->pluck('id'))
            ->orderBy('chapter_number')
            ->get(['id', 'uuid', 'title', 'course_id']);
    }
}

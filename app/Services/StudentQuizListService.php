<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\QuizUserAttempt;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The student's own view of every quiz they have sat.
 *
 * One row per quiz rather than per attempt: sitting a paper three times is one
 * line showing where it stands now, which is what "based on the last attempt"
 * in the filters means.
 */
class StudentQuizListService
{
    /**
     * A paper the instructor has finished with, whatever the outcome.
     */
    public const MARKED = ['submitted', 'graded'];

    /**
     * The filters offered above the list, in the words the student reads.
     */
    public const STATUSES = [
        'pending' => 'Awaiting marking',
        'marked' => 'Marked',
    ];

    public const RESULTS = [
        'passed' => 'Passed',
        'failed' => 'Not passed',
    ];

    /**
     * The student's latest attempt at each quiz, newest first.
     *
     * @param  array{course?:string|null, chapter?:string|null, status?:string|null, result?:string|null}  $filters
     */
    public function listing(User $student, array $filters = []): LengthAwarePaginator
    {
        // One row per quiz: the newest attempt stands for the rest.
        $latest = QuizUserAttempt::query()
            ->where('user_id', $student->id)
            ->selectRaw('MAX(id)')
            ->groupBy('quiz_id');

        $attempts = QuizUserAttempt::query()
            ->whereIn('id', $latest)
            ->with(['quiz.chapters.course']);

        if ($course = ($filters['course'] ?? null)) {
            $attempts->whereHas('quiz.chapters.course', fn ($q) => $q->where('courses.uuid', $course));
        }

        if ($chapter = ($filters['chapter'] ?? null)) {
            $attempts->whereHas('quiz.chapters', fn ($q) => $q->where('chapters.uuid', $chapter));
        }

        match ($filters['status'] ?? null) {
            'pending' => $attempts->where('status', 'pending_review'),
            'marked' => $attempts->whereIn('status', self::MARKED),
            default => null,
        };

        // Pass and fail are only meaningful once a paper has been marked; an
        // unmarked one is neither, rather than quietly counting as a fail.
        match ($filters['result'] ?? null) {
            'passed' => $attempts->whereIn('status', self::MARKED)->where('passed', true),
            'failed' => $attempts->whereIn('status', self::MARKED)->where('passed', false),
            default => null,
        };

        return $attempts->latest('id')->paginate(10)->withQueryString();
    }

    /**
     * How many attempts the student has made at each of these quizzes, keyed
     * by quiz id. One query for the page.
     *
     * @param  iterable<int, QuizUserAttempt>  $rows
     * @return array<int, int>
     */
    public function attemptCounts(User $student, iterable $rows): array
    {
        $quizIds = collect($rows)->pluck('quiz_id')->filter()->unique();

        if ($quizIds->isEmpty()) {
            return [];
        }

        return QuizUserAttempt::query()
            ->where('user_id', $student->id)
            ->whereIn('quiz_id', $quizIds)
            ->selectRaw('quiz_id, COUNT(*) as total')
            ->groupBy('quiz_id')
            ->pluck('total', 'quiz_id')
            ->map(fn ($total) => (int) $total)
            ->all();
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

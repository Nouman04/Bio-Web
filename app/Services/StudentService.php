<?php

namespace App\Services;

use App\Models\Course;
use App\Models\QuizUserAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The staff-side view of who is studying, and how they are getting on.
 *
 * A student belongs to whoever owns the course they subscribe to, so every
 * query here starts from the courses the signed-in user is responsible for.
 * An admin is responsible for all of them.
 */
class StudentService
{
    public function __construct(private readonly ProgressService $progress)
    {
    }

    /**
     * Stripe statuses that still count as a live subscription. A cancelled one
     * inside its paid period counts too, which is the `ends_at` half of the
     * check below.
     */
    private const LIVE_STATUSES = ['active', 'trialing'];

    /**
     * The courses the signed-in user answers for.
     *
     * An instructor answers for what they created; an admin for everything,
     * which is how the course listing already behaves.
     */
    public function coursesFor(User $user): Builder
    {
        $courses = Course::query();

        if (! $user->hasRole('admin')) {
            $courses->where('created_by', $user->id);
        }

        return $courses;
    }

    /**
     * Everyone subscribed to one of those courses, as a query the listing can
     * order and page through.
     *
     * @param  array{search?:string|null, course?:string|null, status?:string|null}  $filters
     */
    public function listing(User $user, array $filters = []): Builder
    {
        $courses = $this->coursesFor($user);

        // Cashier keys each subscription `course_{uuid}`, so ownership is
        // matched on the type rather than on a foreign key.
        if ($only = ($filters['course'] ?? null)) {
            $courses->where('uuid', $only);
        }

        $types = $courses->pluck('uuid')->map(fn ($uuid) => 'course_' . $uuid);

        $students = User::query()
            ->select('users.*')
            ->when(
                $types->isEmpty(),
                // Nobody owns anything, so nobody is subscribed to them.
                fn ($query) => $query->whereRaw('1 = 0'),
                fn ($query) => $query->whereHas(
                    'subscriptions',
                    fn ($q) => $this->live($q->whereIn('type', $types))
                )
            );

        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $students->where(fn ($q) => $q
                ->where('users.name', 'like', "%{$search}%")
                ->orWhere('users.email', 'like', "%{$search}%"));
        }

        // How many of their papers are sitting with this user to be marked.
        $students->withCount([
            'quizAttempts as pending_quizzes_count' => fn ($q) => $q
                ->whereStatus('pending_review')
                ->whereIn('quiz_id', $this->quizIdsFor($user)),
        ]);

        if (($filters['status'] ?? null) === 'pending') {
            $students->having('pending_quizzes_count', '>', 0);
        }

        return $students->with('roles:id,name');
    }

    /**
     * Narrows a subscriptions query to the ones that still grant access:
     * active or trialing, or cancelled but not yet run out.
     */
    private function live(Builder|Relation $query)
    {
        return $query->where(fn ($q) => $q
            ->whereIn('stripe_status', self::LIVE_STATUSES)
            ->orWhere(fn ($grace) => $grace
                ->whereNotNull('ends_at')
                ->where('ends_at', '>', now())));
    }

    /**
     * Every quiz sitting under a course this user answers for.
     *
     * @return Collection<int, int>
     */
    public function quizIdsFor(User $user): Collection
    {
        return DB::table('quizzes_chapters')
            ->join('chapters', 'chapters.id', '=', 'quizzes_chapters.chapter_id')
            ->whereIn('chapters.course_id', $this->coursesFor($user)->select('courses.id'))
            ->pluck('quizzes_chapters.quizz_id');
    }

    /**
     * The courses one student subscribes to, of those this user answers for,
     * each with how far through it they are.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function coursesOf(User $student, User $user): Collection
    {
        $uuids = $student->subscriptions
            ->filter(fn ($subscription) => $subscription->valid())
            ->map(fn ($subscription) => Str::after($subscription->type, 'course_'))
            ->filter()
            ->unique();

        if ($uuids->isEmpty()) {
            return collect();
        }

        $courses = $this->coursesFor($user)
            ->whereIn('uuid', $uuids)
            ->withCount('chapters')
            ->with('category:id,title')
            ->orderBy('title')
            ->get();

        // One grouped query for the whole set rather than one per course.
        $progress = $this->progress->courseProgressFor($student, $courses);

        return $courses->map(fn (Course $course) => [
            'course' => $course,
            'progress' => $progress[$course->id] ?? ['progress' => 0.0, 'completed_weight' => 0, 'total_weight' => 0],
            'subscribed_at' => $student->subscriptions
                ->firstWhere('type', 'course_' . $course->uuid)?->created_at,
        ]);
    }

    /**
     * This student's papers that are waiting to be marked by this user.
     *
     * @return Collection<int, QuizUserAttempt>
     */
    public function pendingQuizzesOf(User $student, User $user): Collection
    {
        return QuizUserAttempt::query()
            ->where('user_id', $student->id)
            ->whereStatus('pending_review')
            ->whereIn('quiz_id', $this->quizIdsFor($user))
            ->with('quiz')
            ->oldest('submitted_at')
            ->get();
    }

    /**
     * Whether this user is allowed to look at this student at all — that is,
     * whether the student subscribes to any course the user answers for.
     */
    public function mayView(User $user, User $student): bool
    {
        $types = $this->coursesFor($user)->pluck('uuid')->map(fn ($uuid) => 'course_' . $uuid);

        if ($types->isEmpty()) {
            return false;
        }

        return $student->subscriptions
            ->filter(fn ($subscription) => $subscription->valid())
            ->contains(fn ($subscription) => $types->contains($subscription->type));
    }

    /**
     * The headline figures above the listing.
     *
     * @return array<string, int>
     */
    public function stats(User $user): array
    {
        $students = $this->listing($user)->get();

        return [
            'total' => $students->count(),
            'pending' => $students->where('pending_quizzes_count', '>', 0)->count(),
            'papers' => (int) $students->sum('pending_quizzes_count'),
        ];
    }
}

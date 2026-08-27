<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\QuizUserAttempt;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The figures behind the admin dashboard.
 *
 * Everything is counted from the database. Where there is not enough history to
 * draw a trend the method says so rather than inventing one, because a made-up
 * "+12%" on a dashboard is worse than no number at all.
 */
class DashboardService
{
    /**
     * The content types that make up the library, and where each is counted.
     */
    private const LIBRARY = [
        'Study notes' => 'notes',
        'Guides' => 'guides',
        'Summaries' => 'summaries',
        'Diagrams' => 'diagrams',
        'Video lessons' => 'video_lessons',
        'Flashcard decks' => 'flashcards',
    ];

    public function __construct(private readonly StudentService $students)
    {
    }

    /**
     * The quizzes this user answers for, worked out once per request.
     */
    private ?Collection $quizIds = null;

    private function quizIds(User $user): Collection
    {
        return $this->quizIds ??= $this->students->quizIdsFor($user);
    }

    /**
     * The headline counts across the top.
     *
     * @return array<string, array{value:int, new:int}>
     */
    public function totals(): array
    {
        $month = now()->startOfMonth();

        $count = function (string $table) use ($month): array {
            $query = DB::table($table)->whereNull('deleted_at');

            return [
                'value' => (clone $query)->count(),
                'new' => (clone $query)->where('created_at', '>=', $month)->count(),
            ];
        };

        return [
            'courses' => $count('courses'),
            'chapters' => $count('chapters'),
            'questions' => $count('question_bank'),
            'quizzes' => $count('quizzes'),
            'categories' => $count('categories'),
            'students' => [
                'value' => User::whereHas('roles', fn ($q) => $q->whereRaw('LOWER(name) = ?', ['student']))->count(),
                'new' => User::whereHas('roles', fn ($q) => $q->whereRaw('LOWER(name) = ?', ['student']))
                    ->where('created_at', '>=', $month)->count(),
            ],
        ];
    }

    /**
     * What is waiting on staff right now.
     *
     * @return array<string, int>
     */
    public function attention(User $user): array
    {
        $quizIds = $this->quizIds($user);

        return [
            'awaiting_marking' => QuizUserAttempt::awaitingReview()->whereIn('quiz_id', $quizIds)->count(),
            'in_progress' => QuizUserAttempt::whereIn('quiz_id', $quizIds)->where('status', 'in_progress')->count(),
            'subscribers' => $this->students->listing($user)->count(),
            'chapters_without_questions' => Chapter::whereDoesntHave('questionBank')->count(),
        ];
    }

    /**
     * What the library is made of. A doughnut of real counts is more use on a
     * young platform than a six-month trend with five empty months.
     *
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public function libraryMix(): array
    {
        $labels = [];
        $data = [];

        foreach (self::LIBRARY as $label => $table) {
            $labels[] = $label;
            $data[] = DB::table($table)->whereNull('deleted_at')->count();
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * How the question bank is spread across chapters — the thing that decides
     * whether a worksheet can actually be built.
     *
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public function questionsPerChapter(int $limit = 8): array
    {
        $rows = Chapter::query()
            ->withCount("questionBank")
            ->orderByDesc("question_bank_count")
            ->limit($limit)
            ->get(['id', 'title']);

        return [
            'labels' => $rows->map(fn ($c) => Str::limit($c->title, 22))->all(),
            'data' => $rows->map(fn ($c) => (int) $c->question_bank_count)->all(),
        ];
    }

    /**
     * Past-paper coverage by year, straight off the references. Shows at a
     * glance which years the bank is thin on.
     *
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public function papersByYear(): array
    {
        $rows = DB::table('past_paper_reference')
            ->whereNotNull('date')
            ->selectRaw('YEAR(date) as year, COUNT(*) as total')
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        return [
            'labels' => $rows->pluck('year')->map(fn ($y) => (string) $y)->all(),
            'data' => $rows->pluck('total')->map(fn ($n) => (int) $n)->all(),
        ];
    }

    /**
     * How quizzes are going: passed, not passed, and still with a marker.
     *
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public function quizOutcomes(User $user): array
    {
        $attempts = QuizUserAttempt::whereIn('quiz_id', $this->quizIds($user))->get();

        $marked = $attempts->whereIn('status', ['submitted', 'graded']);

        return [
            'labels' => ['Passed', 'Not passed', 'Awaiting marking', 'In progress', 'Timed out'],
            'data' => [
                $marked->where('passed', true)->count(),
                $marked->where('passed', false)->count(),
                $attempts->where('status', 'pending_review')->count(),
                $attempts->where('status', 'in_progress')->count(),
                $attempts->where('status', 'expired')->count(),
            ],
        ];
    }

    /**
     * Sign-ups and quiz activity month by month.
     *
     * `sparse` says whether there is enough history for the shape to mean
     * anything, so the view can be honest instead of drawing a flat line and
     * calling it a trend.
     *
     * @return array{labels: array<int, string>, students: array<int, int>, attempts: array<int, int>, sparse: bool}
     */
    public function activity(int $months = 6): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        $byMonth = function (string $table, string $column) use ($start): Collection {
            return DB::table($table)
                ->where($column, '>=', $start)
                ->selectRaw("DATE_FORMAT({$column}, '%Y-%m') as bucket, COUNT(*) as total")
                ->groupBy('bucket')
                ->pluck('total', 'bucket');
        };

        $signups = $byMonth('users', 'created_at');
        $attempts = $byMonth('quiz_user_attempts', 'created_at');

        $labels = [];
        $studentSeries = [];
        $attemptSeries = [];

        for ($i = 0; $i < $months; $i++) {
            $month = (clone $start)->addMonths($i);
            $key = $month->format('Y-m');

            $labels[] = $month->format('M');
            $studentSeries[] = (int) ($signups[$key] ?? 0);
            $attemptSeries[] = (int) ($attempts[$key] ?? 0);
        }

        // One month carrying everything is a new platform, not a trend.
        $withData = count(array_filter($studentSeries)) + count(array_filter($attemptSeries));

        return [
            'labels' => $labels,
            'students' => $studentSeries,
            'attempts' => $attemptSeries,
            'sparse' => $withData <= 2,
        ];
    }

    /**
     * What subscriptions are worth per month, from the plans behind them.
     *
     * @return array{monthly:int, currency:string, subscribers:int, plans:int}
     */
    public function revenue(): array
    {
        $live = DB::table('subscriptions')
            ->where(fn ($q) => $q->whereIn('stripe_status', ['active', 'trialing'])
                ->orWhere(fn ($g) => $g->whereNotNull('ends_at')->where('ends_at', '>', now())))
            ->get(['type', 'stripe_price']);

        $monthly = 0;

        foreach ($live as $subscription) {
            $plan = DB::table('course_plans')
                ->where('stripe_price_id', $subscription->stripe_price)
                ->first();

            // A Stripe price is immutable, so changing what a course costs
            // leaves older subscriptions pointing at a price the plans table no
            // longer names. Fall back to what that course charges today, which
            // is what the subscription is worth going forward.
            if (! $plan) {
                $plan = DB::table('course_plans')
                    ->whereIn('course_id', DB::table('courses')
                        ->where('uuid', Str::after($subscription->type, 'course_'))
                        ->select('id'))
                    ->orderByRaw("CASE WHEN billing_interval = 'month' THEN 0 ELSE 1 END")
                    ->first();
            }

            if (! $plan || $plan->price === null) {
                continue;
            }

            // A yearly plan is worth a twelfth of itself each month.
            $monthly += $plan->billing_interval === 'year'
                ? (int) round($plan->price / 12)
                : (int) $plan->price;
        }

        return [
            'monthly' => $monthly,
            'currency' => 'usd',
            'subscribers' => $live->count(),
            'plans' => DB::table('course_plans')->whereNotNull('price')->count(),
        ];
    }

    /**
     * The newest few of everything worth glancing at.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function recent(int $limit = 6): Collection
    {
        $courses = Course::latest('id')->limit($limit)->get(['id', 'uuid', 'title', 'created_at'])
            ->map(fn (Course $c) => [
                'icon' => 'fa-solid fa-graduation-cap',
                'title' => $c->title,
                'note' => 'Course added',
                'when' => $c->created_at,
                'url' => route('courses.chapters', $c),
            ]);

        $attempts = QuizUserAttempt::with('quiz', 'user')
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->limit($limit)
            ->get()
            ->map(fn (QuizUserAttempt $a) => [
                'icon' => 'fa-solid fa-clipboard-question',
                'title' => $a->quiz?->title ?? 'Quiz',
                'note' => ($a->user?->name ?? 'A student') . ' handed in',
                'when' => $a->submitted_at,
                'url' => route('quizzes.review'),
            ]);

        return $courses->concat($attempts)
            ->filter(fn ($row) => $row['when'] instanceof Carbon)
            ->sortByDesc('when')
            ->take($limit)
            ->values();
    }
}

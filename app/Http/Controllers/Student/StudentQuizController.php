<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Quiz\SubmitQuizRequest;
use App\Http\Resources\QuizAttemptResource;
use App\Http\Resources\QuizResource;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizUserAttempt;
use App\Models\User;
use App\Services\QuizAttemptService;
use App\Services\StudentQuizListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Sitting a quiz, and reading the report afterwards.
 *
 * Students sat quizzes on the public catalogue pages before, which meant a
 * private chapter's quiz was unreachable and nothing was ever recorded. This is
 * the student portal's own route: it opens an attempt, runs the clock, stores
 * what was answered and shows the result.
 */
class StudentQuizController extends Controller
{
    public function __construct(
        private readonly QuizAttemptService $attempts,
        private readonly StudentQuizListService $list,
    ) {
    }

    /**
     * Every quiz set on a chapter, with what this student has already done
     * about each one.
     *
     * The chapter dashboard only has room for the first few, so this is where
     * "View all" lands.
     */
    public function chapterIndex(Request $request, $courseId, $chapterId)
    {
        [$course, $chapter] = $this->locateChapter($courseId, $chapterId);

        $search = trim((string) $request->input('search'));
        $type = $request->input('type');

        $quizzes = $chapter->quizzes()
            ->withCount('questions')
            ->withSum('questions as total_marks', 'quiz_questions.marks')
            ->when($search, fn ($query) => $query->where(fn ($q) => $q
                ->where('quizzes.title', 'like', "%{$search}%")
                ->orWhere('quizzes.description', 'like', "%{$search}%")))
            ->when(
                array_key_exists((string) $type, QuizResource::TYPE_LABELS),
                fn ($query) => $query->where('quizzes.type', $type)
            )
            ->latest('quizzes.id')
            ->paginate(12)
            ->withQueryString();

        // Read before the collection is resolved to arrays: forView() maps the
        // paginator in place.
        $attempts = $this->attemptsFor($request->user(), $quizzes->getCollection());

        return view('student.chapters.quizzes.index', [
            'course' => $course,
            'chapter' => $chapter,
            'courseId' => $courseId,
            'chapterId' => $chapterId,
            'quizzes' => QuizResource::forView($quizzes),
            'search' => $search,
            'type' => $type,
            'attempts' => $attempts,
        ]);
    }

    /**
     * What this student has done on each of these quizzes, keyed by quiz id:
     * how many attempts, the best result so far, and the newest one — which is
     * what the card offers to resume or re-read.
     *
     * One query for the whole page rather than one per card.
     *
     * @param  \Illuminate\Support\Collection<int, Quiz>  $quizzes
     * @return array<int, array<string, mixed>>
     */
    private function attemptsFor(?User $user, $quizzes): array
    {
        if (! $user || $quizzes->isEmpty()) {
            return [];
        }

        return QuizUserAttempt::query()
            ->where('user_id', $user->id)
            ->whereIn('quiz_id', $quizzes->pluck('id'))
            ->orderBy('id')
            ->get()
            ->groupBy('quiz_id')
            ->map(function ($sat) {
                $latest = $sat->last();

                // Only a finished paper has a result worth calling a best.
                $marked = $sat->whereIn('status', ['submitted', 'graded']);
                $best = $marked->sortByDesc('earned_marks')->first();

                return [
                    'count' => $sat->count(),
                    'latest_status' => $latest->status,
                    'latest_uuid' => $latest->uuid,
                    // An attempt still running is resumed, not restarted.
                    'open' => ! in_array($latest->status, QuizUserAttempt::CLOSED, true),
                    'awaiting' => $latest->status === 'pending_review',
                    'best_marks' => $best?->earned_marks !== null ? (float) $best->earned_marks : null,
                    'best_total' => $best?->total_marks !== null ? (float) $best->total_marks : null,
                    'passed' => (bool) $marked->firstWhere('passed', true),
                ];
            })
            ->all();
    }

    /**
     * The quiz paper. Opening it starts the clock.
     */
    public function show(Request $request, $courseId, $chapterId, $quizId)
    {
        [$course, $chapter, $quiz] = $this->locate($request, $courseId, $chapterId, $quizId);

        $attempt = $this->attempts->start($quiz, $request->user());

        // The clock ran out while they were away: straight to the report.
        if ($attempt->status === 'expired') {
            return redirect()
                ->route('student.quizzes.report', $attempt->uuid)
                ->with('error', 'That attempt ran out of time.');
        }

        return view('student.quizzes.show', [
            'course' => $course,
            'chapter' => $chapter,
            'courseId' => $courseId,
            'chapterId' => $chapterId,
            'quiz' => $quiz,
            'attempt' => $attempt,
            'questions' => $this->attempts->questions($quiz),
            'secondsRemaining' => $attempt->secondsRemaining(),
        ]);
    }

    /**
     * Hands the paper in. Also the target of the timer's automatic submit.
     */
    public function submit(SubmitQuizRequest $request, $courseId, $chapterId, $quizId): JsonResponse
    {
        [, , $quiz] = $this->locate($request, $courseId, $chapterId, $quizId);

        $attempt = QuizUserAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->firstOrFail();

        // A paper handed in after the clock stopped still counts what was
        // answered — the timer submits on the student's behalf.
        $attempt = $this->attempts->submit(
            $attempt,
            $request->validated()['answers'] ?? [],
            $request->boolean('auto')
        );

        return response()->json([
            'status' => $attempt->status,
            'message' => $attempt->status === 'pending_review'
                ? 'Handed in. Your written answers are with the instructor.'
                : 'Handed in.',
            'url' => route('student.quizzes.report', $attempt->uuid),
        ]);
    }

    /**
     * The report for one attempt: the score, and every question with what was
     * answered. A paper still awaiting marking says so rather than pretending
     * to have a result.
     */
    public function report(Request $request, string $attempt)
    {
        $record = QuizUserAttempt::where('uuid', $attempt)
            ->with('quiz', 'grader')
            ->firstOrFail();

        abort_if($record->user_id !== $request->user()->id, 404);

        $chapter = $record->quiz->chapters()->with('course')->first();

        return view('student.quizzes.report', [
            'attempt' => $record,
            'quiz' => $record->quiz,
            'chapter' => $chapter,
            'course' => $chapter?->course,
            'report' => $this->attempts->report($record),
        ]);
    }

    /**
     * Every quiz this student has sat — one row per quiz, showing where their
     * latest attempt at it stands, and filterable by course, chapter, whether
     * it has been marked, and how it went.
     */
    public function index(Request $request)
    {
        $student = $request->user();

        $filters = [
            'course' => $request->input('course'),
            'chapter' => $request->input('chapter'),
            'status' => $request->input('status'),
            'result' => $request->input('result'),
        ];

        $attempts = $this->list->listing($student, $filters);

        // Read before the collection is resolved to arrays: forView() maps the
        // paginator in place.
        $counts = $this->list->attemptCounts($student, $attempts->getCollection());

        return view('student.quizzes.index', [
            'attempts' => QuizAttemptResource::forView($attempts),
            'counts' => $counts,
            'courses' => $this->list->courses($student),
            'chapters' => $this->list->chapters($student, $filters['course']),
            'filters' => $filters,
        ]);
    }

    /**
     * The course/chapter pair from the URL, proving the chapter really belongs
     * to the course.
     *
     * @return array{0: Course, 1: Chapter}
     */
    private function locateChapter(string $courseId, string $chapterId): array
    {
        $course = Course::where('uuid', $courseId)->firstOrFail();
        $chapter = Chapter::where('uuid', $chapterId)->firstOrFail();

        abort_if($chapter->course_id !== $course->id, 404);

        return [$course, $chapter];
    }

    /**
     * Resolves the chain. The paywall is the `subscribed` middleware on the
     * route group.
     *
     * @return array{0: Course, 1: Chapter, 2: Quiz}
     */
    private function locate(Request $request, string $courseId, string $chapterId, string $quizId): array
    {
        $course = Course::where('uuid', $courseId)->firstOrFail();
        $chapter = Chapter::where('uuid', $chapterId)->firstOrFail();
        $quiz = Quiz::where('uuid', $quizId)->firstOrFail();

        abort_if($chapter->course_id !== $course->id, 404);

        // The quiz must actually be set on this chapter.
        abort_unless($quiz->chapters()->whereKey($chapter->id)->exists(), 404);

        return [$course, $chapter, $quiz];
    }
}

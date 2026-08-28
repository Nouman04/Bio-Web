<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quiz\GradeAttemptRequest;
use App\Models\QuizUserAttempt;
use App\Services\QuizAttemptService;
use Illuminate\Http\Request;

/**
 * Marking the written answers a machine cannot.
 *
 * Only the instructor who owns the course a quiz sits under may mark its
 * attempts — checked on every action, not just on the listing.
 */
class QuizReviewController extends Controller
{
    public function __construct(private readonly QuizAttemptService $attempts)
    {
    }

    /**
     * Everything waiting on this instructor.
     */
    public function index(Request $request)
    {
        return view('quizzes.review.index', [
            'attempts' => $this->attempts->awaitingReviewFor($request->user()),
        ]);
    }

    /**
     * One attempt, with each answer and a box to mark it out of.
     */
    public function show(Request $request, string $attempt)
    {
        $record = $this->find($request, $attempt);
        $chapter = $record->quiz->chapters()->with('course')->first();

        return view('quizzes.review.show', [
            'attempt' => $record,
            'quiz' => $record->quiz,
            'student' => $record->user,
            'chapter' => $chapter,
            'course' => $chapter?->course,
            'report' => $this->attempts->report($record),
        ]);
    }

    /**
     * Saves the marks and tells the student.
     */
    public function grade(GradeAttemptRequest $request, string $attempt)
    {
        $record = $this->find($request, $attempt);

        $data = $request->validated();

        $this->attempts->grade(
            $record,
            $request->user(),
            $data['marks'] ?? [],
            $data['feedback'] ?? null
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Marks saved. The student has been notified.',
                'url' => route('quizzes.review'),
            ]);
        }

        return redirect()
            ->route('quizzes.review')
            ->with('success', 'Marks saved. The student has been notified.');
    }

    /**
     * The attempt, if this instructor is allowed to mark it.
     */
    private function find(Request $request, string $uuid): QuizUserAttempt
    {
        $attempt = QuizUserAttempt::where('uuid', $uuid)
            ->with('quiz', 'user')
            ->firstOrFail();

        abort_unless($this->attempts->mayGrade($request->user(), $attempt), 403);

        return $attempt;
    }
}

<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizQuestion;
use App\Models\QuizUserAttempt;
use App\Models\User;
use App\Notifications\QuizAwaitingReview;
use App\Notifications\QuizGraded;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Sitting a quiz, marking it, and grading the parts a machine cannot.
 *
 * A quiz made only of multiple choice marks itself the moment it is submitted.
 * Anything with a written answer — theory, or mixed — is stored as
 * `pending_review` and waits on the course instructor.
 */
class QuizAttemptService
{
    /**
     * Starts an attempt, or returns the one already running.
     *
     * An attempt whose clock ran out while the student was away is closed off
     * first, so they are never handed an expired paper to keep writing on.
     */
    public function start(Quiz $quiz, User $user): QuizUserAttempt
    {
        $open = QuizUserAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->open()
            ->latest('id')
            ->first();

        if ($open) {
            return $open->hasExpired() ? $this->expire($open) : $open;
        }

        return QuizUserAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'status' => 'in_progress',
            'started_at' => now(),
            // A quiz with no duration set is untimed.
            'expires_at' => $quiz->duration ? now()->addMinutes((int) $quiz->duration) : null,
            'total_marks' => $this->totalMarks($quiz),
        ]);
    }

    /**
     * Records the answers and decides what happens next.
     *
     * `$answers` is keyed by quiz_question id: an option id for a multiple
     * choice, free text for a written answer.
     *
     * @param  array<int, mixed>  $answers
     */
    public function submit(QuizUserAttempt $attempt, array $answers, bool $auto = false): QuizUserAttempt
    {
        // A closed attempt cannot be submitted twice, however the request got
        // here — a late auto-submit racing a manual one, say.
        if ($attempt->isClosed()) {
            return $attempt;
        }

        return DB::transaction(function () use ($attempt, $answers, $auto) {
            $questions = $this->questions($attempt->quiz);

            $attempt->answers()->delete();

            $earned = 0.0;
            $total = 0.0;
            $needsReview = false;

            foreach ($questions as $link) {
                $marks = (float) $link->marks;
                $total += $marks;

                $given = $answers[$link->id] ?? null;
                $isMcq = $this->isMcq($link);

                if ($isMcq) {
                    $correctId = $link->questionBank?->answer->first()?->question_option_id;
                    $picked = is_numeric($given) ? (int) $given : null;
                    $correct = $correctId !== null && $picked === $correctId;

                    $earned += $correct ? $marks : 0;

                    $attempt->answers()->create([
                        'quizzes_question_id' => $link->id,
                        'selected_option' => $picked,
                        'is_correct' => $correct,
                        'marks_awarded' => $correct ? $marks : 0,
                    ]);

                    continue;
                }

                // Written: nothing can be scored until someone reads it.
                $needsReview = true;

                $attempt->answers()->create([
                    'quizzes_question_id' => $link->id,
                    'answer_content' => is_string($given) ? trim($given) : null,
                    'is_correct' => false,
                    'marks_awarded' => null,
                ]);
            }

            $attempt->forceFill([
                'status' => $needsReview ? 'pending_review' : ($auto ? 'submitted' : 'submitted'),
                'submitted_at' => now(),
                'total_marks' => $total,
                // Only a fully marked paper has a final score.
                'earned_marks' => $needsReview ? null : $earned,
                'passed' => $needsReview ? null : $this->passed($attempt->quiz, $earned),
            ])->save();

            if ($needsReview) {
                $this->notifyInstructor($attempt);
            }

            return $attempt->fresh();
        });
    }

    /**
     * An instructor's marks for the written answers on one attempt.
     *
     * @param  array<int, array{marks?: mixed, feedback?: mixed}>  $marks  Keyed by answer id.
     */
    public function grade(QuizUserAttempt $attempt, User $grader, array $marks, ?string $feedback = null): QuizUserAttempt
    {
        return DB::transaction(function () use ($attempt, $grader, $marks, $feedback) {
            $earned = 0.0;

            foreach ($attempt->answers()->with('quizQuestion')->get() as $answer) {
                $awarded = $answer->marks_awarded;

                if (array_key_exists($answer->id, $marks)) {
                    $cap = (float) ($answer->quizQuestion?->marks ?? 0);
                    $given = (float) ($marks[$answer->id]['marks'] ?? 0);

                    // Never award more than the question is worth.
                    $awarded = max(0, min($cap, $given));

                    $answer->forceFill([
                        'marks_awarded' => $awarded,
                        'is_correct' => $cap > 0 && $awarded >= $cap,
                        'feedback' => $marks[$answer->id]['feedback'] ?? null,
                    ])->save();
                }

                $earned += (float) $awarded;
            }

            $attempt->forceFill([
                'status' => 'graded',
                'earned_marks' => $earned,
                'passed' => $this->passed($attempt->quiz, $earned),
                'graded_by' => $grader->id,
                'graded_at' => now(),
                'feedback' => $feedback,
            ])->save();

            $attempt->user?->notify(new QuizGraded($attempt->fresh()));

            return $attempt->fresh();
        });
    }

    /**
     * Closes an attempt whose clock ran out with nothing submitted.
     */
    public function expire(QuizUserAttempt $attempt): QuizUserAttempt
    {
        $attempt->forceFill([
            'status' => 'expired',
            'submitted_at' => now(),
            'earned_marks' => 0,
            'passed' => false,
        ])->save();

        return $attempt->fresh();
    }

    /**
     * The questions on a quiz, in order, with everything marking needs.
     */
    public function questions(Quiz $quiz): Collection
    {
        return QuizQuestion::whereIn('quiz_chapter_id', $quiz->quizChapters()->select('id'))
            ->with(['questionBank.answer', 'questionBank.options', 'questionBank.category:id,type'])
            ->orderBy('order')
            ->get();
    }

    /**
     * The report a student or instructor reads: every question, what was
     * answered, and how it scored.
     *
     * @return array<int, array<string, mixed>>
     */
    public function report(QuizUserAttempt $attempt): array
    {
        $answers = $attempt->answers()->get()->keyBy('quizzes_question_id');

        return $this->questions($attempt->quiz)
            ->map(function (QuizQuestion $link) use ($answers) {
                $answer = $answers->get($link->id);
                $question = $link->questionBank;
                $correctId = $question?->answer->first()?->question_option_id;

                return [
                    'answer_id' => $answer?->id,
                    'question' => $question?->question,
                    'is_mcq' => $this->isMcq($link),
                    'marks' => (float) $link->marks,
                    'awarded' => $answer?->marks_awarded !== null ? (float) $answer->marks_awarded : null,
                    'correct' => (bool) $answer?->is_correct,
                    'feedback' => $answer?->feedback,
                    'answered' => $this->isMcq($link)
                        ? $answer?->selected_option !== null
                        : filled($answer?->answer_content),
                    'written' => $answer?->answer_content,
                    'options' => $question?->options->map(fn ($option) => [
                        'id' => $option->id,
                        'title' => $option->title,
                        'correct' => $option->id === $correctId,
                        'picked' => $answer && (int) $answer->selected_option === $option->id,
                    ])->values()->all() ?? [],
                    'expected' => (string) ($question?->answer->first()?->description ?: ''),
                ];
            })
            ->all();
    }

    /**
     * Attempts an instructor still has to mark, across the courses they own.
     */
    public function awaitingReviewFor(User $instructor): Collection
    {
        return QuizUserAttempt::awaitingReview()
            ->whereIn('quiz_id', $this->quizIdsOwnedBy($instructor))
            ->with('quiz', 'user')
            ->latest('submitted_at')
            ->get();
    }

    /**
     * Whether this user may mark this attempt: the quiz has to belong to a
     * course they created.
     */
    public function mayGrade(User $user, QuizUserAttempt $attempt): bool
    {
        return $this->quizIdsOwnedBy($user)->contains($attempt->quiz_id);
    }

    /**
     * @return Collection<int, int>
     */
    private function quizIdsOwnedBy(User $user): Collection
    {
        return DB::table('quizzes_chapters')
            ->join('chapters', 'chapters.id', '=', 'quizzes_chapters.chapter_id')
            ->join('courses', 'courses.id', '=', 'chapters.course_id')
            ->where('courses.created_by', $user->id)
            ->pluck('quizzes_chapters.quizz_id');
    }

    /**
     * The instructor who owns the course this quiz sits under.
     */
    public function instructorFor(Quiz $quiz): ?User
    {
        return $quiz->chapters()->with('course.creator')->first()?->course?->creator;
    }

    private function notifyInstructor(QuizUserAttempt $attempt): void
    {
        $this->instructorFor($attempt->quiz)?->notify(new QuizAwaitingReview($attempt));
    }

    private function isMcq(QuizQuestion $link): bool
    {
        return $link->questionBank?->category?->type === 'mcqs';
    }

    private function passed(Quiz $quiz, float $earned): bool
    {
        // A quiz with no passing score set is passed by completing it.
        return $quiz->passing_score === null || $earned >= (float) $quiz->passing_score;
    }

    private function totalMarks(Quiz $quiz): float
    {
        return (float) QuizQuestion::whereIn('quiz_chapter_id', $quiz->quizChapters()->select('id'))->sum('marks');
    }
}

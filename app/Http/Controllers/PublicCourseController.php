<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Flashcard;
use App\Models\Note;
use App\Models\Quiz;
use App\Services\StripeService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;

/**
 * The public catalogue, walked as a chain:
 *
 *   courses › chapters › chapter dashboard › listing › detail
 *
 * Only chapters marked public on the course's configuration page are reachable
 * here; a private one 404s just like a mismatched URL does.
 */
class PublicCourseController extends Controller
{
    /**
     * Quiz types behind the two practice sections. The MCQ section shows only
     * quizzes built purely of MCQs.
     */
    private const MCQ_TYPES = ['mcqs'];

    private const THEORY_TYPES = ['theory'];

    /**
     * A course's chapters.
     */
    public function chapters(Course $course)
    {
        return view('public.catalog.chapters', [
            'course' => $course,
            // Every chapter is listed; a private one is shown locked and leads
            // to the subscription page rather than its content.
            'chapters' => $course->chapters()->orderBy('chapter_number')->get(),
        ]);
    }

    /**
     * The paywall a locked chapter leads to.
     */
    public function subscribe(?Course $course = null, ?Chapter $chapter = null)
    {
        abort_if($chapter && $course && $chapter->course_id !== $course->id, 404);

        return view('public.subscribe', [
            'course' => $course,
            'chapter' => $chapter,
        ]);
    }

    /**
     * Where "Start Your Subscription" leads: the plan for the course being
     * read, and nothing else — the reader is here about this course.
     */
    public function plans(Course $course, ?Chapter $chapter = null, StripeService $stripe = null)
    {
        if ($chapter) {
            abort_if($chapter->course_id !== $course->id, 404);
        }

        return view('public.plans', [
            'course' => $course->loadCount('chapters')->load(['category:id,title', 'plan']),
            'plan' => $course->plan,
            'chapter' => $chapter,
            'subscribed' => $stripe?->subscribedTo(request()->user(), $course) ?? false,
        ]);
    }

    /**
     * Hands the reader over to Stripe Checkout for this course's plan.
     */
    public function checkout(Request $request, Course $course, StripeService $stripe)
    {
        $back = route('public.course.chapters', $course);

        if (! $course->hasStripePlan()) {
            return redirect()->to($back)
                ->with('error', 'This course is not on sale yet.');
        }

        // Subscribing needs an account, so sign in first and come straight back.
        if (! $request->user()) {
            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()->route('student.login');
        }

        if ($stripe->subscribedTo($request->user(), $course)) {
            return redirect()->to($back)->with('success', 'You already subscribe to this course.');
        }

        try {
            return $stripe->checkoutForCourse(
                $request->user(),
                $course,
                route('public.course.chapters', $course) . '?subscribed=1',
                route('public.subscribe.plans', $course)
            );
        } catch (ApiErrorException $e) {
            report($e);

            return redirect()->to($back)
                ->with('error', 'Stripe could not start that checkout. Please try again.');
        }
    }

    /**
     * One chapter's hub: its topics, and how much of each resource it holds.
     */
    public function chapter(Course $course, Chapter $chapter)
    {
        $this->scope($course, $chapter);

        return view('public.catalog.chapter', [
            'course' => $course,
            'chapter' => $chapter,
            'topics' => $chapter->topics()->orderBy('id')->take(3)->get(),
            // Each panel shows a few entries and links through to its listing.
            'mcqPreview' => $this->quizzes($chapter, self::MCQ_TYPES)
                ->withCount('questions')->latest('id')->take(3)->get(),
            'theoryPreview' => $this->quizzes($chapter, self::THEORY_TYPES)
                ->withCount('questions')->latest('id')->take(3)->get(),
            'counts' => [
                'notes' => $chapter->notes()->count(),
                'flashcards' => $chapter->flashcards()->count(),
                'mcqs' => $this->quizzes($chapter, self::MCQ_TYPES)->count(),
                'theory' => $this->quizzes($chapter, self::THEORY_TYPES)->count(),
            ],
        ]);
    }

    /**
     * Listing: the chapter's study notes.
     */
    public function notes(Course $course, Chapter $chapter)
    {
        $this->scope($course, $chapter);

        return view('public.catalog.notes', [
            'course' => $course,
            'chapter' => $chapter,
            'notes' => $chapter->notes()->with('topic:id,title')->latest('id')->get(),
        ]);
    }

    /**
     * Detail: one study note.
     */
    public function note(Course $course, Chapter $chapter, Note $note)
    {
        $this->scope($course, $chapter);
        abort_if($note->chapter_id !== $chapter->id, 404);

        return view('public.catalog.note', [
            'course' => $course,
            'chapter' => $chapter,
            'note' => $note->load('topic:id,title', 'summary:id,title'),
            // Sibling notes power the "next / previous" rail.
            'siblings' => $chapter->notes()->orderBy('id')->get(['id', 'uuid', 'title']),
        ]);
    }

    /**
     * Listing: the chapter's flashcard decks.
     */
    public function flashcards(Course $course, Chapter $chapter)
    {
        $this->scope($course, $chapter);

        return view('public.catalog.flashcards', [
            'course' => $course,
            'chapter' => $chapter,
            'decks' => $chapter->flashcards()->withCount('assessments')->latest('id')->get(),
        ]);
    }

    /**
     * Detail: one deck, with its cards in order.
     */
    public function flashcard(Course $course, Chapter $chapter, Flashcard $flashcard)
    {
        $this->scope($course, $chapter);
        abort_if($flashcard->chapter_id !== $chapter->id, 404);

        $cards = $flashcard->assessments()
            ->with('question.answer', 'question.options', 'question.category:id,type')
            ->get()
            ->filter(fn ($assessment) => $assessment->question)
            ->map(fn ($assessment) => [
                'question' => $assessment->question->question,
                'answer' => $this->answerText($assessment->question),
            ])
            ->values();

        return view('public.catalog.flashcard', [
            'course' => $course,
            'chapter' => $chapter,
            'deck' => $flashcard,
            'cards' => $cards,
        ]);
    }

    /**
     * Listing: the chapter's MCQ quizzes.
     */
    public function mcqs(Course $course, Chapter $chapter)
    {
        $this->scope($course, $chapter);

        return view('public.catalog.mcqs', [
            'course' => $course,
            'chapter' => $chapter,
            'quizzes' => $this->quizzes($chapter, self::MCQ_TYPES)
                ->withCount('questions')
                ->latest('id')
                ->get(),
        ]);
    }

    /**
     * Detail: one MCQ quiz, with its questions and options.
     */
    public function mcq(Course $course, Chapter $chapter, Quiz $quiz)
    {
        return view('public.catalog.mcq', [
            'course' => $course,
            'chapter' => $chapter,
            'quiz' => $quiz,
            // Marked after submitting, so the answers stay off the page.
            'questions' => $this->quizQuestions($course, $chapter, $quiz, self::MCQ_TYPES, withAnswers: false),
        ]);
    }

    /**
     * Marks a submitted MCQ attempt. Nothing is stored — the reader gets their
     * score and the correct answers back, and that is the end of it.
     */
    public function submitMcq(Request $request, Course $course, Chapter $chapter, Quiz $quiz): JsonResponse
    {
        $links = $this->quizLinks($course, $chapter, $quiz, self::MCQ_TYPES);

        $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'integer'],
        ]);

        $chosen = $request->input('answers', []);
        $earned = 0;
        $total = 0;
        $results = [];

        foreach ($links as $link) {
            $question = $link->questionBank;
            $marks = (float) $link->marks;
            $total += $marks;

            // The answer row points at the option that scores.
            $correctId = $question->answer->first()?->question_option_id;
            $pickedId = isset($chosen[$question->id]) ? (int) $chosen[$question->id] : null;
            $isCorrect = $correctId !== null && $pickedId === $correctId;

            if ($isCorrect) {
                $earned += $marks;
            }

            $results[$question->id] = [
                'correct' => $isCorrect,
                'answered' => $pickedId !== null,
                'correct_option_id' => $correctId,
                'picked_option_id' => $pickedId,
                'marks' => $this->trimNumber($marks),
                'awarded' => $this->trimNumber($isCorrect ? $marks : 0),
                'answer' => $this->answerText($question),
            ];
        }

        $passMark = $quiz->passing_score !== null ? (float) $quiz->passing_score : null;

        return response()->json([
            'earned' => $this->trimNumber($earned),
            'total' => $this->trimNumber($total),
            'percent' => $total > 0 ? (int) round($earned / $total * 100) : 0,
            'correct_count' => collect($results)->where('correct', true)->count(),
            'question_count' => count($results),
            'passed' => $passMark !== null ? $earned >= $passMark : null,
            'pass_mark' => $passMark !== null ? $this->trimNumber($passMark) : null,
            'results' => $results,
        ]);
    }

    /**
     * Listing: the chapter's theory practice.
     */
    public function theory(Course $course, Chapter $chapter)
    {
        $this->scope($course, $chapter);

        return view('public.catalog.theory', [
            'course' => $course,
            'chapter' => $chapter,
            'quizzes' => $this->quizzes($chapter, self::THEORY_TYPES)
                ->withCount('questions')
                ->latest('id')
                ->get(),
        ]);
    }

    /**
     * Detail: one theory paper and its questions.
     */
    public function theoryDetail(Course $course, Chapter $chapter, Quiz $quiz)
    {
        return view('public.catalog.theory-detail', [
            'course' => $course,
            'chapter' => $chapter,
            'quiz' => $quiz,
            'questions' => $this->quizQuestions($course, $chapter, $quiz, self::THEORY_TYPES),
        ]);
    }

    /**
     * Guards the course › chapter chain, and keeps private chapters off the
     * public site entirely.
     */
    private function scope(Course $course, Chapter $chapter): void
    {
        abort_if($chapter->course_id !== $course->id, 404);

        // A private chapter is listed but not readable: everything under it
        // leads to the subscription page instead.
        if ($chapter->visibility !== 'public') {
            throw new HttpResponseException(
                redirect()->route('public.course.chapter.subscribe', [$course, $chapter])
            );
        }
    }

    /**
     * The chapter's quizzes of the given types.
     */
    private function quizzes(Chapter $chapter, array $types)
    {
        return Quiz::query()
            ->whereIn('type', $types)
            ->where('status', 'published')
            ->whereHas('chapters', fn ($query) => $query->where('chapters.id', $chapter->id));
    }

    /**
     * A quiz's questions, shaped for the detail pages. Guards the chain and
     * checks the quiz really belongs to this chapter and section.
     */
    private function quizQuestions(Course $course, Chapter $chapter, Quiz $quiz, array $types, bool $withAnswers = true)
    {
        return $this->quizLinks($course, $chapter, $quiz, $types)
            ->map(fn ($link) => [
                'id' => $link->questionBank->id,
                'text' => $link->questionBank->question,
                'marks' => rtrim(rtrim(number_format((float) $link->marks, 2, '.', ''), '0'), '.'),
                'difficulty' => $link->questionBank->difficulty_level,
                'options' => $link->questionBank->options
                    ->map(fn ($option) => ['id' => $option->id, 'title' => $option->title])
                    ->values()->all(),
                // Withheld on a quiz that is marked after submitting, so the
                // answers are not sitting in the page source.
                'answer' => $withAnswers ? $this->answerText($link->questionBank) : null,
            ])
            ->values();
    }

    /**
     * The quiz's question links, once the chain and the quiz itself check out.
     */
    private function quizLinks(Course $course, Chapter $chapter, Quiz $quiz, array $types)
    {
        $this->scope($course, $chapter);

        abort_if(! in_array($quiz->type, $types, true), 404);
        abort_if($quiz->status !== 'published', 404);
        abort_if(! $quiz->chapters->contains('id', $chapter->id), 404);

        return $quiz->questions()
            ->with('questionBank.answer', 'questionBank.options', 'questionBank.category:id,type')
            ->get()
            ->filter(fn ($link) => $link->questionBank);
    }

    /**
     * Formats a mark for display: 2.00 reads as 2, and 1.50 as 1.5.
     */
    private function trimNumber(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') ?: '0';
    }

    /**
     * The written answer for a question: the chosen option for an MCQ, or the
     * free-text answer for a theory question.
     */
    private function answerText($question): string
    {
        // `answer` is a HasMany, so it arrives as a collection of one.
        return (string) ($question->answer->first()?->description ?: '');
    }
}

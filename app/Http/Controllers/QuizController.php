<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\QuestionBank;
use App\Models\Quiz;
use App\Models\QuizChapter;
use App\Models\QuizQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class QuizController extends Controller
{
    /**
     * Question kinds a quiz can be built from.
     */
    public const TYPES = [
        'mixed' => 'Mixed (MCQs & Theory)',
        'mcqs' => 'Multiple Choice Only',
        'theory' => 'Theory / Essay Only',
    ];

    /**
     * Display the quizzes listing. Reached through course › chapter it shows
     * only that chapter's quizzes; from the sidenav it shows them all.
     */
    public function index(Request $request, ?Course $course = null, ?Chapter $chapter = null)
    {
        return view('quizzes.index', [
            'chain' => $this->chain($course, $chapter),
            'chapters' => Chapter::orderBy('chapter_number')->get(['id', 'uuid', 'title']),
            'filters' => [
                'search' => $request->input('search', ''),
                'chapter' => $request->input('chapter', ''),
                'status' => $request->input('status', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
            ],
        ]);
    }

    /**
     * Server-side DataTables source for the quizzes list.
     */
    public function data(Request $request, ?Course $course = null, ?Chapter $chapter = null): JsonResponse
    {
        $chain = $this->chain($course, $chapter);

        $quizzes = Quiz::query()
            ->with(['chapters:id,title'])
            ->withCount(['userAttempts', 'questions']);

        // Through the chain the chapter is fixed, so the filter is ignored.
        $chain
            ? $quizzes->whereHas('chapters', fn ($q) => $q->where('chapters.id', $chain['chapter']->id))
            : $quizzes->when($request->input('chapter'), fn ($query, $uuid) => $query->whereHas(
                'chapters',
                fn ($q) => $q->where('chapters.uuid', $uuid)
            ));

        // Filters from the filter card above the table.
        $quizzes->when(
            $request->input('search_term'),
            fn ($query, $term) => $query->where('title', 'like', "%{$term}%")
        );

        $quizzes->when($request->input('status'), fn ($query, $status) => $query->where('status', $status));
        $quizzes->when($request->input('date_from'), fn ($query, $date) => $query->whereDate('created_at', '>=', $date));
        $quizzes->when($request->input('date_to'), fn ($query, $date) => $query->whereDate('created_at', '<=', $date));

        $table = DataTables::eloquent($quizzes)
            ->addColumn('title_cell', fn (Quiz $quiz) => view('quizzes.partials.title-cell', compact('quiz') + ['chain' => $chain])->render())
            ->addColumn('chapter_cell', fn (Quiz $quiz) => view('quizzes.partials.chapter-cell', compact('quiz') + ['chain' => $chain])->render())
            ->addColumn('date_cell', fn (Quiz $quiz) => view('quizzes.partials.date-cell', compact('quiz') + ['chain' => $chain])->render())
            ->addColumn('status_cell', fn (Quiz $quiz) => view('quizzes.partials.status-cell', compact('quiz') + ['chain' => $chain])->render())
            ->addColumn('responses_cell', fn (Quiz $quiz) => view('quizzes.partials.responses-cell', compact('quiz') + ['chain' => $chain])->render())
            ->addColumn('action', fn (Quiz $quiz) => view('quizzes.partials.actions', compact('quiz') + ['chain' => $chain])->render())
            ->orderColumn('title_cell', 'title $1')
            ->orderColumn('date_cell', 'created_at $1')
            ->orderColumn('status_cell', 'status $1')
            ->orderColumn('responses_cell', 'user_attempts_count $1')
            ->rawColumns(['title_cell', 'chapter_cell', 'date_cell', 'status_cell', 'responses_cell', 'action'])
            ->only(['title_cell', 'chapter_cell', 'date_cell', 'status_cell', 'responses_cell', 'action'])
            ->toJson();

        // Never let a proxy or the browser replay an old page of rows.
        return $table->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * Show the form for creating a new quiz. The chapter is never picked here:
     * through the chain it is fixed to the one in the URL, and from the sidenav
     * the quiz is simply left unattached to a chapter.
     */
    public function create(?Course $course = null, ?Chapter $chapter = null)
    {
        return view('quizzes.create', [
            'chain' => $this->chain($course, $chapter),
            'types' => self::TYPES,
        ]);
    }

    /**
     * Store a newly created quiz.
     */
    public function store(Request $request, ?Course $course = null, ?Chapter $chapter = null)
    {
        $chain = $this->chain($course, $chapter);
        $data = $this->validated($request);

        $this->validateQuestionSet($data);

        $quiz = DB::transaction(function () use ($data, $chain) {
            $quiz = Quiz::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'duration' => $data['duration'] ?? null,
                'passing_score' => $data['passing_score'] ?? null,
                'shuffle_questions' => (bool) ($data['shuffle_questions'] ?? false),
                'status' => $data['status'],
            ]);

            $this->syncQuestions($quiz, $data['questions'] ?? [], $chain);

            return $quiz;
        });

        return $this->respond(
            $request,
            $quiz->fresh(),
            $quiz->status === 'published' ? 'Quiz published successfully.' : 'Quiz saved as a draft.',
            201,
            $chain
        );
    }

    /**
     * Soft delete a quiz along with the rows that hang off it.
     */
    public function destroy(Request $request, Quiz $quiz)
    {
        DB::transaction(function () use ($quiz) {
            $chapterRows = $quiz->quizChapters()->pluck('id');

            QuizQuestion::whereIn('quiz_chapter_id', $chapterRows)->delete();
            QuizChapter::whereIn('id', $chapterRows)->delete();
            $quiz->delete();
        });

        return $this->respond($request, null, 'Quiz deleted successfully.');
    }

    /**
     * Show the form for editing a quiz — the same two-step wizard as create,
     * with the quiz's own questions already on the list.
     */
    public function edit($course = null, $chapter = null, ?Quiz $quiz = null)
    {
        // Route parameters arrive positionally, so on the sidenav route the
        // quiz lands in the first slot. resolveEdit() untangles that.
        [$chain, $quiz] = $this->resolveEdit($course, $chapter, $quiz);

        return view('quizzes.edit', [
            'chain' => $chain,
            'quiz' => $quiz,
            'types' => self::TYPES,
            'questions' => $this->pickedQuestions($quiz),
        ]);
    }

    /**
     * Update a quiz, replacing its question list with what the form sent.
     */
    public function update(Request $request, $course = null, $chapter = null, ?Quiz $quiz = null)
    {
        [$chain, $quiz] = $this->resolveEdit($course, $chapter, $quiz);

        $data = $this->validated($request);
        $this->validateQuestionSet($data);

        DB::transaction(function () use ($quiz, $data, $chain) {
            $quiz->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'duration' => $data['duration'] ?? null,
                'passing_score' => $data['passing_score'] ?? null,
                'shuffle_questions' => (bool) ($data['shuffle_questions'] ?? false),
                'status' => $data['status'],
            ]);

            // The list is rebuilt from the form, so the old rows go first. They
            // are pure join data — nothing worth keeping once replaced.
            $oldRows = $quiz->quizChapters()->pluck('id');
            QuizQuestion::whereIn('quiz_chapter_id', $oldRows)->forceDelete();
            QuizChapter::whereIn('id', $oldRows)->forceDelete();

            $this->syncQuestions($quiz, $data['questions'] ?? [], $chain);
        });

        return $this->respond(
            $request,
            $quiz->fresh(),
            $quiz->status === 'published' ? 'Quiz published successfully.' : 'Quiz saved as a draft.',
            200,
            $chain
        );
    }

    /**
     * Both edit actions are reachable two ways, so the chain and the quiz are
     * untangled — and matched against each other — in one place.
     *
     * @return array{0: ?array, 1: Quiz}
     */
    private function resolveEdit($course, $chapter, ?Quiz $quiz): array
    {
        // Sidenav route — /quizzes/{quiz}/edit — puts the quiz in slot one.
        if ($course instanceof Quiz) {
            return [null, $course];
        }

        abort_if(! $quiz instanceof Quiz, 404);

        $chain = $this->chain((int) $course, (int) $chapter);

        // A quiz reached through a chapter has to actually be in it.
        abort_if(
            $chain && ! $quiz->chapters->contains('id', $chain['chapter']->id),
            404
        );

        return [$chain, $quiz];
    }

    /**
     * The quiz's questions shaped the way the picker's rows are, so the edit
     * page can render them without a round trip.
     */
    private function pickedQuestions(Quiz $quiz): array
    {
        return $quiz->questions()
            ->with('questionBank.category:id,type')
            ->get()
            ->filter(fn (QuizQuestion $link) => $link->questionBank)
            ->map(function (QuizQuestion $link) {
                $question = $link->questionBank;
                $type = $question->category?->type;

                return [
                    'id' => $question->id,
                    'text' => $question->question,
                    'type' => $type,
                    'marks' => rtrim(rtrim(number_format((float) $link->marks, 2, '.', ''), '0'), '.'),
                    'meta' => implode(' • ', array_filter([
                        $type === 'mcqs' ? 'MCQ' : ($type ? 'Theory' : null),
                        $question->difficulty_level,
                    ])),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Shared validation. Note there is no chapter rule — the chapter is decided
     * by the URL, so nothing the form posts could change it.
     */
    private function validated(Request $request): array
    {
        // An untouched number box posts an empty string, which is not an integer.
        $request->merge([
            'duration' => $request->input('duration') ?: null,
            'passing_score' => $request->input('passing_score') ?: null,
        ]);

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(array_keys(self::TYPES))],
            'duration' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'passing_score' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'shuffle_questions' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'questions' => ['nullable', 'array'],
            'questions.*.question_bank_id' => ['required', 'integer', 'exists:question_bank,id'],
            'questions.*.marks' => ['required', 'numeric', 'min:0', 'max:999999'],
        ], [], [
            'questions.*.question_bank_id' => 'question',
            'questions.*.marks' => 'marks',
        ]);
    }

    /**
     * The rules the form cannot express on its own: a pass mark has to be
     * reachable, and every question has to suit the kind of quiz being built.
     *
     * @throws ValidationException
     */
    private function validateQuestionSet(array $data): void
    {
        $rows = $data['questions'] ?? [];
        $total = array_sum(array_column($rows, 'marks'));

        if (($data['passing_score'] ?? null) !== null && $data['passing_score'] > $total) {
            throw ValidationException::withMessages([
                'passing_score' => $rows
                    ? "The passing score cannot be more than the total of {$total} marks."
                    : 'Add some questions before setting a passing score.',
            ]);
        }

        // A theory or MCQ quiz only holds questions of that kind; mixed takes both.
        if (! $rows || $data['type'] === 'mixed') {
            return;
        }

        $wrong = QuestionBank::whereIn('id', array_column($rows, 'question_bank_id'))
            ->whereHas('category', fn ($query) => $query->where('type', '!=', $data['type']))
            ->count();

        if ($wrong) {
            $label = $data['type'] === 'mcqs' ? 'multiple-choice' : 'theory';

            throw ValidationException::withMessages([
                'questions' => $wrong === 1
                    ? "One of the questions is not a {$label} question."
                    : "{$wrong} of the questions are not {$label} questions.",
            ]);
        }
    }

    /**
     * Attaches the picked questions in the order the form listed them.
     *
     * Questions hang off a quizzes_chapters row rather than the quiz itself.
     * Through the chain every question belongs to the chapter in the URL, so
     * there is one such row; from the sidenav a quiz may span chapters, so each
     * question is filed under its own (and unchaptered questions under a row
     * with no chapter).
     */
    private function syncQuestions(Quiz $quiz, array $rows, ?array $chain): void
    {
        if (! $chain && ! $rows) {
            return;
        }

        $chapterIds = $chain
            ? []
            : QuestionBank::whereIn('id', array_column($rows, 'question_bank_id'))
                ->pluck('chapter_id', 'id');

        // One quizzes_chapters row per chapter in play, made on first use.
        $quizChapters = [];
        $rowFor = function (?Chapter $chapterId) use ($quiz, &$quizChapters) {
            $key = $chapterId ?? 0;

            return $quizChapters[$key] ??= QuizChapter::create([
                'quizz_id' => $quiz->id,
                'chapter_id' => $chapterId,
            ])->id;
        };

        // Through the chain the quiz belongs to that chapter even before any
        // question is picked.
        if ($chain) {
            $rowFor($chain['chapter']->id);
        }

        foreach (array_values($rows) as $position => $row) {
            $chapterId = $chain
                ? $chain['chapter']->id
                : ($chapterIds[$row['question_bank_id']] ?? null);

            QuizQuestion::create([
                'quiz_chapter_id' => $rowFor($chapterId),
                'question_bank_id' => $row['question_bank_id'],
                'marks' => $row['marks'],
                'order' => $position,
            ]);
        }
    }


    /**
     * Resolves the course › chapter chain when a quiz is reached through a
     * chapter, 404ing on a mismatched URL. Returns null for the sidenav entry,
     * where no chapter is in play at all.
     */
    private function chain(?Course $course, ?Chapter $chapter): ?array
    {
        if (! $course || ! $chapter) {
            return null;
        }

        $courseModel = $course;

        return [
            'course' => $courseModel,
            'chapter' => $chapter,
        ];
    }

    /**
     * JSON for fetch/AJAX callers, a redirect back to the listing for plain
     * form posts.
     */
    private function respond(Request $request, ?Quiz $quiz, string $message, int $status = 200, ?array $chain = null)
    {
        $listing = $chain
            ? route('courses.chapters.quizzes', [$chain['course']->id, $chain['chapter']->id])
            : route('quizzes');

        if ($request->expectsJson()) {
            return response()->json(array_filter([
                'message' => $message,
                'data' => $quiz,
                'redirect' => $listing,
            ], fn ($value) => $value !== null), $status);
        }

        return redirect()->to($listing)->with('success', $message);
    }
}

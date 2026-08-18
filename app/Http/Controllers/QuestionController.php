<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Diagram;
use App\Models\Flashcard;
use App\Models\Note;
use App\Models\QuestionAnswer;
use App\Models\QuestionBank;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Models\QuestionableType;
use App\Models\Quiz;
use App\Models\Summary;
use App\Models\Topic;
use App\Models\VideoLesson;
use App\Models\Worksheet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class QuestionController extends Controller
{
    /**
     * Content a question can be linked to through `questionable_type`. The key
     * is what the UI posts; the value is the model behind it.
     */
    public const LINKABLES = [
        'topic' => Topic::class,
        'summary' => Summary::class,
        'note' => Note::class,
        'diagram' => Diagram::class,
        'video_lesson' => VideoLesson::class,
    ];

    /**
     * What the picker can be building for, keyed by the `exclude_type` the UI
     * sends. Mirrors the `assessments` morph.
     */
    private const ASSESSMENTABLES = [
        'flashcard' => Flashcard::class,
        'quiz' => Quiz::class,
        'worksheet' => Worksheet::class,
    ];

    /**
     * Difficulty levels offered in the forms and the filter card.
     */
    public const DIFFICULTIES = ['Easy', 'Medium', 'Hard'];

    /**
     * Display the question bank. The grid itself is loaded by DataTables from
     * the `questions.data` endpoint below. Reached through course › chapter the
     * bank is locked to that chapter; from the sidenav it spans them all.
     */
    public function index(Request $request, ?int $course = null, ?int $chapter = null)
    {
        return view('questions.index', [
            'chain' => $this->chain($course, $chapter),
            'chapters' => Chapter::orderBy('chapter_number')->get(['id', 'title']),
            'categories' => QuestionCategory::orderBy('type')->get(['id', 'type']),
            'linkables' => $this->linkableOptions(),
            'difficulties' => self::DIFFICULTIES,
            'filters' => [
                'question' => $request->input('question', ''),
                'chapter' => $request->input('chapter', ''),
                'linked_type' => $request->input('linked_type', ''),
                'linked_id' => $request->input('linked_id', ''),
                'difficulty' => $request->input('difficulty', ''),
                'category' => $request->input('category', ''),
                'assignment' => $request->input('assignment', ''),
            ],
        ]);
    }

    /**
     * The add-questions page — several can be entered in one submit. The
     * chapter is never picked here: through the chain it is fixed to the one in
     * the URL, and from the sidenav the questions are simply left unchaptered.
     */
    public function create(?int $course = null, ?int $chapter = null)
    {
        return view('questions.create', [
            'chain' => $this->chain($course, $chapter),
            'categories' => QuestionCategory::orderBy('type')->get(['id', 'type']),
            'difficulties' => self::DIFFICULTIES,
        ]);
    }

    /**
     * Server-side DataTables source for the question bank.
     */
    public function data(Request $request, ?int $course = null, ?int $chapter = null): JsonResponse
    {
        $chain = $this->chain($course, $chapter);

        $questions = QuestionBank::query()
            ->with(['chapter:id,title', 'category:id,type', 'answer', 'options'])
            ->withCount(['questionables', 'assessments']);

        // Filters from the filter card above the table.
        $questions->when(
            $request->input('search_term'),
            fn ($query, $term) => $query->where('question', 'like', "%{$term}%")
        );

        // Through the chain the chapter is fixed, so the filter is ignored.
        $chain
            ? $questions->where('chapter_id', $chain['chapter']->id)
            : $questions->when($request->input('chapter'), fn ($query, $id) => $query->where('chapter_id', $id));

        $questions->when($request->input('difficulty'), fn ($query, $level) => $query->where('difficulty_level', $level));
        $questions->when($request->input('category'), fn ($query, $id) => $query->where('question_categories_id', $id));

        // Linked to a kind of content — optionally to one specific record.
        $questions->when($request->input('linked_type'), function ($query, $type) use ($request) {
            $class = self::LINKABLES[$type] ?? null;
            if (! $class) {
                return;
            }

            $query->whereHas('questionables', function ($link) use ($class, $request) {
                $link->where('questionable_type', $class)
                    ->when($request->input('linked_id'), fn ($q, $id) => $q->where('questionable_id', $id));
            });
        });

        // Assigned anywhere at all: content links or assessments.
        $questions->when($request->input('assignment'), function ($query, $assignment) {
            $assignment === 'assigned'
                ? $query->where(fn ($q) => $q->has('questionables')->orHas('assessments'))
                : $query->doesntHave('questionables')->doesntHave('assessments');
        });

        $table = DataTables::eloquent($questions)
            ->addColumn('question_cell', fn (QuestionBank $question) => view('questions.partials.question-cell', compact('question'))->render())
            ->addColumn('answer_cell', fn (QuestionBank $question) => view('questions.partials.answer-cell', compact('question'))->render())
            ->addColumn('meta_cell', fn (QuestionBank $question) => view('questions.partials.meta-cell', compact('question'))->render())
            ->addColumn('usage_cell', fn (QuestionBank $question) => view('questions.partials.usage-cell', compact('question'))->render())
            ->addColumn('action', fn (QuestionBank $question) => view('questions.partials.actions', compact('question'))->render())
            ->orderColumn('question_cell', 'question $1')
            ->rawColumns(['question_cell', 'answer_cell', 'meta_cell', 'usage_cell', 'action'])
            ->only(['question_cell', 'answer_cell', 'meta_cell', 'usage_cell', 'action'])
            ->toJson();

        // Never let a proxy or the browser replay an old page of rows.
        return $table->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * Records of one linkable type, for the dependent filter picker.
     */
    public function linkedRecords(Request $request, string $type): JsonResponse
    {
        $class = self::LINKABLES[$type] ?? null;
        abort_if(! $class, 404);

        $column = $class === Note::class ? 'title' : 'title';

        $records = $class::query()
            ->when($request->input('q'), fn ($query, $term) => $query->where($column, 'like', "%{$term}%"))
            ->latest('id')
            ->limit(50)
            ->get();

        return response()->json(
            $records->map(fn ($record) => [
                'id' => $record->id,
                'text' => \Illuminate\Support\Str::limit(
                    trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($record->title ?: $record->content)))),
                    70
                ) ?: "#{$record->id}",
            ])
        );
    }

    /**
     * Type-ahead source for the question widget's Tom Select field.
     * Returns the questions matching `q`, newest first.
     */
    public function search(Request $request): JsonResponse
    {
        $questions = QuestionBank::query()
            ->when($request->input('q'), fn ($query, $term) => $query->where('question', 'like', "%{$term}%"))
            // Building through a chapter chain: only that chapter's questions.
            ->when($request->input('chapter'), fn ($query, $id) => $query->where('chapter_id', $id))
            // A theory or MCQ quiz only offers questions of that kind.
            ->when(
                in_array($request->input('type'), ['theory', 'mcqs'], true),
                fn ($query) => $query->whereHas(
                    'category',
                    fn ($q) => $q->where('type', $request->input('type'))
                )
            )
            // Rows the caller already has on screen but has not saved yet.
            ->when($request->input('exclude'), function ($query, $exclude) {
                $ids = array_filter(array_map('intval', explode(',', (string) $exclude)));

                $query->when($ids, fn ($q) => $q->whereNotIn('id', $ids));
            })
            // Hide questions already attached to the assessment being built, so
            // the picker never offers a duplicate.
            ->when(
                $request->input('exclude_type') && $request->input('exclude_id'),
                function ($query) use ($request) {
                    $class = self::ASSESSMENTABLES[$request->input('exclude_type')] ?? null;
                    if (! $class) {
                        return;
                    }

                    $query->whereNotIn('id', Assessment::query()
                        ->where('assessmentable_type', $class)
                        ->where('assessmentable_id', $request->input('exclude_id'))
                        ->pluck('question_id'));
                }
            )
            ->with('category:id,type')
            ->latest('id')
            ->limit(20)
            ->get(['id', 'question', 'difficulty_level', 'question_categories_id']);

        return response()->json(
            $questions->map(fn (QuestionBank $question) => [
                'id' => $question->id,
                'text' => $question->question,
                // The raw kind, so a caller can tell whether a picked question
                // still suits the quiz after its type is changed.
                'type' => $question->category?->type,
                'meta' => implode(' • ', array_filter([
                    $question->category?->type === 'mcqs' ? 'MCQ' : ($question->category ? 'Theory' : null),
                    $question->difficulty_level,
                ])),
            ])
        );
    }

    /**
     * Store one or more questions in a single submit.
     */
    public function store(Request $request, ?int $course = null, ?int $chapter = null)
    {
        $chain = $this->chain($course, $chapter);

        // The chapter is never posted — it comes from the URL, or from nowhere.
        $data = $request->validate([
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question' => ['required', 'string'],
            'questions.*.question_categories_id' => ['required', 'integer', 'exists:question_categories,id'],
            'questions.*.difficulty_level' => ['nullable', Rule::in(self::DIFFICULTIES)],
            'questions.*.answer' => ['nullable', 'string'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*' => ['nullable', 'string', 'max:1000'],
            'questions.*.correct_option' => ['nullable', 'integer', 'min:0'],
        ], [], $this->attributeNames($request));

        foreach ($data['questions'] as $index => $row) {
            $this->validateChoices($row, "questions.{$index}");
        }

        $created = DB::transaction(function () use ($data, $chain) {
            $ids = [];

            foreach ($data['questions'] as $row) {
                $question = QuestionBank::create([
                    'chapter_id' => $chain['chapter']->id ?? null,
                    'question_categories_id' => $row['question_categories_id'],
                    'question' => $row['question'],
                    'difficulty_level' => $row['difficulty_level'] ?? null,
                ]);

                $this->syncChoices($question, $row);
                $ids[] = $question->id;
            }

            return $ids;
        });

        $count = count($created);

        return $this->respond(
            $request,
            null,
            $count === 1 ? 'Question added to the bank.' : "{$count} questions added to the bank.",
            201,
            $chain
        );
    }

    /**
     * Update a question, along with its type, difficulty and answer.
     */
    public function update(Request $request, QuestionBank $question)
    {
        $data = $request->validate([
            'chapter_id' => ['nullable', 'integer', 'exists:chapters,id'],
            'question' => ['required', 'string'],
            'question_categories_id' => ['required', 'integer', 'exists:question_categories,id'],
            'difficulty_level' => ['nullable', Rule::in(self::DIFFICULTIES)],
            'answer' => ['nullable', 'string'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'string', 'max:1000'],
            'correct_option' => ['nullable', 'integer', 'min:0'],
        ]);

        $this->validateChoices($data);

        DB::transaction(function () use ($question, $data) {
            $question->update([
                'chapter_id' => $data['chapter_id'] ?? null,
                'question_categories_id' => $data['question_categories_id'],
                'question' => $data['question'],
                'difficulty_level' => $data['difficulty_level'] ?? null,
            ]);

            $this->syncChoices($question, $data);
        });

        return $this->respond($request, $question->fresh(), 'Question updated successfully.');
    }

    /**
     * Soft delete a question and drop the places it was linked from.
     */
    public function destroy(Request $request, QuestionBank $question)
    {
        DB::transaction(function () use ($question) {
            QuestionableType::where('question_id', $question->id)->delete();
            Assessment::where('question_id', $question->id)->delete();
            $question->delete();
        });

        return $this->respond($request, null, 'Question deleted successfully.');
    }

    /**
     * Resolves the course › chapter chain when the bank is entered through a
     * chapter, 404ing on a mismatched URL. Returns null for the sidenav entry,
     * where no chapter is in play at all.
     */
    private function chain(?int $course, ?int $chapter): ?array
    {
        if (! $course || ! $chapter) {
            return null;
        }

        $courseModel = Course::findOrFail($course);

        return [
            'course' => $courseModel,
            'chapter' => Chapter::where('course_id', $courseModel->id)->findOrFail($chapter),
        ];
    }

    /**
     * MCQs must carry at least two options and a valid correct one. Theory
     * questions ignore options entirely.
     */
    private function validateChoices(array $row, string $prefix = ''): void
    {
        if (! $this->isMcq($row['question_categories_id'] ?? null)) {
            return;
        }

        $key = fn (string $field) => $prefix ? "{$prefix}.{$field}" : $field;
        $options = $this->cleanOptions($row['options'] ?? []);

        if (count($options) < 2) {
            throw ValidationException::withMessages([
                $key('options') => 'An MCQ needs at least two options.',
            ]);
        }

        $correct = $row['correct_option'] ?? null;

        if ($correct === null || ! array_key_exists($correct, $options)) {
            throw ValidationException::withMessages([
                $key('correct_option') => 'Choose which option is the correct answer.',
            ]);
        }
    }

    /**
     * Blank rows in the options list are ignored, and the keys are preserved so
     * `correct_option` keeps pointing at the right one.
     */
    private function cleanOptions(array $options): array
    {
        return array_filter(
            array_map(fn ($option) => is_string($option) ? trim($option) : '', $options),
            fn ($option) => $option !== ''
        );
    }

    private function isMcq(?int $categoryId): bool
    {
        return $categoryId
            && QuestionCategory::where('id', $categoryId)->value('type') === 'mcqs';
    }

    /**
     * Writes the answer side of a question: options plus the chosen one for an
     * MCQ, or a single free-text answer for a theory question.
     */
    private function syncChoices(QuestionBank $question, array $row): void
    {
        // Whatever it was before, rebuild from what the form sent.
        $question->options()->delete();
        $existing = $question->answer()->first();

        if ($this->isMcq($row['question_categories_id'] ?? null)) {
            $created = [];
            foreach ($this->cleanOptions($row['options'] ?? []) as $index => $title) {
                $created[$index] = QuestionOption::create([
                    'question_bank_id' => $question->id,
                    'title' => $title,
                ]);
            }

            $correct = $created[$row['correct_option']] ?? null;

            $attributes = [
                'question_option_id' => $correct?->id,
                // Mirrored so listings can show the answer without a join.
                'description' => $correct?->title ?? '',
            ];

            $existing
                ? $existing->update($attributes)
                : QuestionAnswer::create($attributes + ['question_bank_id' => $question->id]);

            return;
        }

        // Theory: a single free-text answer, removed when left blank.
        $answer = isset($row['answer']) ? trim($row['answer']) : null;
        $isEmpty = $answer === null || $answer === '' || $answer === '<p><br></p>';

        if ($isEmpty) {
            $existing?->delete();

            return;
        }

        $existing
            ? $existing->update(['question_option_id' => null, 'description' => $answer])
            : QuestionAnswer::create([
                'question_bank_id' => $question->id,
                'description' => $answer,
            ]);
    }

    /**
     * Friendlier names for the nested add-modal fields in error messages.
     */
    private function attributeNames(Request $request): array
    {
        $names = [];

        foreach (array_keys($request->input('questions', [])) as $index) {
            $number = $index + 1;
            $names["questions.{$index}.question"] = "question {$number}";
            $names["questions.{$index}.question_categories_id"] = "question {$number} type";
            $names["questions.{$index}.options"] = "question {$number} options";
            $names["questions.{$index}.correct_option"] = "question {$number} correct answer";
        }

        return $names;
    }

    /**
     * Options for the "linked to" filter, keyed by the value posted.
     */
    private function linkableOptions(): array
    {
        return [
            'topic' => 'Topic',
            'summary' => 'Summary',
            'note' => 'Note',
            'diagram' => 'Diagram',
            'video_lesson' => 'Video Lesson',
        ];
    }

    /**
     * JSON for fetch/AJAX callers, a redirect back to the bank for plain form
     * posts.
     */
    private function respond(Request $request, ?QuestionBank $question, string $message, int $status = 200, ?array $chain = null)
    {
        if ($request->expectsJson()) {
            return response()->json(array_filter([
                'message' => $message,
                'data' => $question,
            ], fn ($value) => $value !== null), $status);
        }

        // Back to whichever bank the request came from.
        return $chain
            ? redirect()->route('courses.chapters.questions', [$chain['course']->id, $chain['chapter']->id])
                ->with('success', $message)
            : redirect()->route('questions')->with('success', $message);
    }
}

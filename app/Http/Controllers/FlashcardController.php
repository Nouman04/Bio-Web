<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Diagram;
use App\Models\Flashcard;
use App\Models\Guide;
use App\Models\Note;
use App\Models\QuestionBank;
use App\Models\Summary;
use App\Models\Topic;
use App\Models\VideoLesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class FlashcardController extends Controller
{
    /**
     * Content a deck can be built from. The key is what the UI posts as
     * `source_type`; the value is the model behind it.
     */
    public const SOURCES = [
        'note' => Note::class,
        'video_lesson' => VideoLesson::class,
        'guide' => Guide::class,
        'summary' => Summary::class,
        'diagram' => Diagram::class,
        'topic' => Topic::class,
    ];

    /**
     * Display the flashcards listing. The grid itself is loaded by DataTables
     * from the `flashcards.data` endpoint below.
     */
    public function index(Request $request, Course $course, Chapter $chapter)
    {
        [$courseModel, $chapterModel] = $this->scope($course, $chapter);

        return view('flashcards.index', [
            'course' => $courseModel,
            'chapter' => $chapterModel,
            'sources' => $this->sourceOptions(),
            'filters' => [
                'title' => $request->input('title', ''),
                'source_type' => $request->input('source_type', ''),
                'source_id' => $request->input('source_id', ''),
            ],
        ]);
    }

    /**
     * Server-side DataTables source for the flashcards list.
     */
    public function data(Request $request, Course $course, Chapter $chapter): JsonResponse
    {
        $this->scope($course, $chapter);

        $flashcards = Flashcard::query()
            ->where('chapter_id', $chapter->id)
            ->with(['chapter:id,title', 'flashcardable'])
            ->withCount('assessments');

        // Filters from the filter card above the table.
        $flashcards->when(
            $request->input('search_term'),
            fn ($query, $title) => $query->where('title', 'like', "%{$title}%")
        );

        // Filter by the kind of content a deck hangs off, and optionally by
        // which specific record.
        $flashcards->when($request->input('source_type'), function ($query, $type) use ($request) {
            $class = self::SOURCES[$type] ?? null;
            if (! $class) {
                return;
            }

            $query->where('flashcardable_type', $class)
                ->when($request->input('source_id'), fn ($q, $id) => $q->where('flashcardable_id', $id));
        });

        $table = DataTables::eloquent($flashcards)
            ->addColumn('title_cell', fn (Flashcard $flashcard) => view('flashcards.partials.title-cell', [
                'flashcard' => $flashcard,
                'courseId' => $course,
                'chapterId' => $chapter,
            ])->render())
            ->addColumn('source_cell', fn (Flashcard $flashcard) => view('flashcards.partials.source-cell', compact('flashcard'))->render())
            ->addColumn('questions_cell', fn (Flashcard $flashcard) => view('flashcards.partials.questions-cell', compact('flashcard'))->render())
            ->addColumn('date_cell', fn (Flashcard $flashcard) => view('flashcards.partials.date-cell', compact('flashcard'))->render())
            ->addColumn('action', fn (Flashcard $flashcard) => view('flashcards.partials.actions', [
                'flashcard' => $flashcard,
                'courseId' => $course,
                'chapterId' => $chapter,
            ])->render())
            ->orderColumn('title_cell', 'title $1')
            ->orderColumn('questions_cell', 'assessments_count $1')
            ->orderColumn('date_cell', 'created_at $1')
            ->rawColumns(['title_cell', 'source_cell', 'questions_cell', 'date_cell', 'action'])
            ->only(['title_cell', 'source_cell', 'questions_cell', 'date_cell', 'action'])
            ->toJson();

        // Never let a proxy or the browser replay an old page of rows.
        return $table->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * Records of one source type, for the dependent picker in the modal and
     * the filter card.
     */
    public function sourceRecords(Request $request, Course $course, Chapter $chapter, string $type): JsonResponse
    {
        $this->scope($course, $chapter);

        $class = self::SOURCES[$type] ?? null;
        abort_if(! $class, 404);

        $records = $class::query()
            ->when($request->input('q'), fn ($query, $term) => $query->where($this->labelColumn($class), 'like', "%{$term}%"))
            ->latest('id')
            ->limit(50)
            ->get();

        return response()->json(
            $records->map(fn ($record) => [
                'id' => $record->id,
                'text' => $this->labelFor($record),
            ])
        );
    }

    /**
     * Step one of the flow: create the deck itself, then send the user to the
     * builder to attach questions.
     */
    public function store(Request $request, Course $course, Chapter $chapter)
    {
        $this->scope($course, $chapter);

        $data = $this->validated($request);

        $flashcard = Flashcard::create([
            'title' => $data['title'],
            // The chapter comes from the chain, not from a picker in the form.
            'chapter_id' => $chapter->id,
            'flashcardable_type' => ($data['source_type'] ?? null) ? self::SOURCES[$data['source_type']] : null,
            'flashcardable_id' => $data['source_id'] ?? null,
        ]);

        $builder = route('flashcards.builder', [$course, $chapter, $flashcard]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Flashcard created — now add its questions.',
                'data' => $flashcard->fresh(),
                // The page follows this to step two.
                'redirect' => $builder,
            ], 201);
        }

        return redirect()->to($builder)
            ->with('success', 'Flashcard created — now add its questions.');
    }

    /**
     * Step two: the question builder for a deck.
     */
    public function builder(Course $course, Chapter $chapter, Flashcard $flashcard)
    {
        [$courseModel, $chapterModel] = $this->scope($course, $chapter, $flashcard);

        $flashcard->load(['chapter:id,title', 'flashcardable', 'assessments.question']);

        return view('flashcards.builder', [
            'course' => $courseModel,
            'chapter' => $chapterModel,
            'flashcard' => $flashcard,
            'sourceLabel' => $this->sourceLabel($flashcard),
        ]);
    }

    /**
     * The deck's questions, in order — the builder list reloads from here.
     */
    public function questions(Course $course, Chapter $chapter, Flashcard $flashcard): JsonResponse
    {
        $this->scope($course, $chapter, $flashcard);

        return response()->json(
            $flashcard->assessments()->with('question:id,question,difficulty_level')->get()
                ->filter(fn (Assessment $assessment) => $assessment->question)
                ->map(fn (Assessment $assessment) => [
                    'assessment_id' => $assessment->id,
                    'id' => $assessment->question->id,
                    'text' => $assessment->question->question,
                    'meta' => $assessment->question->difficulty_level,
                    'order' => $assessment->order,
                ])
                ->values()
        );
    }

    /**
     * Attach questions from the bank, appended after whatever is already there.
     */
    public function addQuestions(Request $request, Course $course, Chapter $chapter, Flashcard $flashcard): JsonResponse
    {
        $this->scope($course, $chapter, $flashcard);

        $data = $request->validate([
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'exists:question_bank,id'],
        ]);

        $existing = $flashcard->assessments()->pluck('question_id')->all();
        $order = (int) $flashcard->assessments()->max('order');

        $added = 0;
        DB::transaction(function () use ($flashcard, $data, $existing, &$order, &$added) {
            foreach ($data['question_ids'] as $questionId) {
                if (in_array((int) $questionId, $existing, true)) {
                    continue;
                }

                $flashcard->assessments()->create([
                    'question_id' => $questionId,
                    'order' => ++$order,
                ]);
                $added++;
            }
        });

        return response()->json([
            'message' => $added === 1 ? '1 question added.' : "{$added} questions added.",
            'added' => $added,
        ]);
    }

    /**
     * Persist a drag-and-drop reorder.
     */
    public function reorderQuestions(Request $request, Course $course, Chapter $chapter, Flashcard $flashcard): JsonResponse
    {
        $this->scope($course, $chapter, $flashcard);

        $data = $request->validate([
            'assessment_ids' => ['required', 'array'],
            'assessment_ids.*' => ['integer'],
        ]);

        DB::transaction(function () use ($flashcard, $data) {
            foreach (array_values($data['assessment_ids']) as $position => $assessmentId) {
                $flashcard->assessments()->whereKey($assessmentId)->update(['order' => $position + 1]);
            }
        });

        return response()->json(['message' => 'Order saved.']);
    }

    /**
     * Detach one question from the deck.
     */
    public function removeQuestion(Course $course, Chapter $chapter, Flashcard $flashcard, Assessment $assessment): JsonResponse
    {
        $this->scope($course, $chapter, $flashcard);

        abort_if(
            $assessment->assessmentable_id !== $flashcard->id
            || $assessment->assessmentable_type !== Flashcard::class,
            404
        );

        $assessment->delete();

        return response()->json(['message' => 'Question removed.']);
    }

    /**
     * Update the deck's own details.
     */
    public function update(Request $request, Course $course, Chapter $chapter, Flashcard $flashcard)
    {
        $this->scope($course, $chapter, $flashcard);

        $data = $this->validated($request, $flashcard);

        // The chapter stays as it is — it belongs to the chain, not the form.
        $flashcard->update([
            'title' => $data['title'],
            'flashcardable_type' => ($data['source_type'] ?? null) ? self::SOURCES[$data['source_type']] : null,
            'flashcardable_id' => $data['source_id'] ?? null,
        ]);

        return $this->respond($request, $course, $chapter, $flashcard->fresh(), 'Flashcard updated successfully.');
    }

    /**
     * Soft delete the deck and detach its questions.
     */
    public function destroy(Request $request, Course $course, Chapter $chapter, Flashcard $flashcard)
    {
        $this->scope($course, $chapter, $flashcard);

        DB::transaction(function () use ($flashcard) {
            $flashcard->assessments()->delete();
            $flashcard->delete();
        });

        return $this->respond($request, $course, $chapter, null, 'Flashcard deleted successfully.');
    }

    /**
     * Shared validation. The source is optional, but picking a type without a
     * record (or the other way round) is rejected.
     */
    private function validated(Request $request, ?Flashcard $flashcard = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'source_type' => ['nullable', Rule::in(array_keys(self::SOURCES))],
            'source_id' => ['nullable', 'integer', 'required_with:source_type'],
        ]);
    }

    /**
     * Guards the course › chapter › flashcard chain so a mismatched URL 404s
     * instead of quietly operating on another chapter's decks.
     */
    private function scope(Course $course, Chapter $chapter, ?Flashcard $flashcard = null): array
    {
        $courseModel = $course;
        $chapterModel = $chapter;

        abort_if($chapterModel->course_id !== $courseModel->id, 404);

        abort_if($flashcard && $flashcard->chapter_id !== $chapterModel->id, 404);

        return [$courseModel, $chapterModel];
    }

    /**
     * Options for the "linked to" pickers, keyed by the value posted.
     */
    private function sourceOptions(): array
    {
        return [
            'note' => 'Note',
            'video_lesson' => 'Video Lesson',
            'guide' => 'Guide',
            'summary' => 'Summary',
            'diagram' => 'Diagram',
            'topic' => 'Topic',
        ];
    }

    /**
     * Notes have no title of their own, so fall back to their content.
     */
    private function labelColumn(string $class): string
    {
        return $class === Note::class ? 'content' : 'title';
    }

    private function labelFor($record): string
    {
        $column = $this->labelColumn($record::class);
        $value = strip_tags((string) $record->{$column});

        return \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', $value)), 70) ?: "#{$record->id}";
    }

    /**
     * Human-readable "linked to" label for a deck.
     */
    private function sourceLabel(Flashcard $flashcard): ?string
    {
        if (! $flashcard->flashcardable) {
            return null;
        }

        $type = array_search($flashcard->flashcardable_type, self::SOURCES, true);

        return $this->sourceOptions()[$type] . ': ' . $this->labelFor($flashcard->flashcardable);
    }

    /**
     * JSON for fetch/AJAX callers, a redirect back to the listing for plain
     * form posts.
     */
    private function respond(Request $request, Course $course, Chapter $chapter, ?Flashcard $flashcard, string $message, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json(array_filter([
                'message' => $message,
                'data' => $flashcard,
            ], fn ($value) => $value !== null), $status);
        }

        return redirect()->route('flashcards', [$course, $chapter])->with('success', $message);
    }
}

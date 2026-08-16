<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Guide;
use App\Models\QuestionBank;
use App\Models\QuestionCategory;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class GuideController extends Controller
{
    /**
     * Guide types, keyed by the enum value stored on the row.
     */
    public const TYPES = [
        'theory_guides' => 'Theory Guide',
        'atp_guides' => 'ATP Guide',
    ];

    /**
     * Display the guides listing. The grid itself is loaded by DataTables from
     * the `guides.data` endpoint below.
     */
    public function index(Request $request, int $course, int $chapter)
    {
        [$courseModel, $chapterModel] = $this->scope($course, $chapter);

        return view('guides.index', [
            'course' => $courseModel,
            'chapter' => $chapterModel,
            'topics' => Topic::orderBy('title')->get(['id', 'title']),
            'types' => self::TYPES,
            'filters' => [
                'title' => $request->input('title', ''),
                'topic' => $request->input('topic', ''),
                'type' => $request->input('type', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
            ],
        ]);
    }

    /**
     * Server-side DataTables source for the guides list.
     */
    public function data(Request $request, int $course, int $chapter): JsonResponse
    {
        $this->scope($course, $chapter);

        $guides = Guide::query()
            ->where('chapter_id', $chapter)
            ->with('topic:id,title')
            ->withCount('questionables');

        // Filters from the filter card above the table.
        $guides->when(
            $request->input('search_term'),
            fn ($query, $title) => $query->where('title', 'like', "%{$title}%")
        );

        $guides->when($request->input('topic'), fn ($query, $id) => $query->where('topic_id', $id));
        $guides->when($request->input('type'), fn ($query, $type) => $query->where('type', $type));
        $guides->when($request->input('date_from'), fn ($query, $date) => $query->whereDate('created_at', '>=', $date));
        $guides->when($request->input('date_to'), fn ($query, $date) => $query->whereDate('created_at', '<=', $date));

        $table = DataTables::eloquent($guides)
            ->addColumn('title_cell', fn (Guide $guide) => view('guides.partials.title-cell', compact('guide'))->render())
            ->addColumn('topic_cell', fn (Guide $guide) => view('guides.partials.topic-cell', compact('guide'))->render())
            ->addColumn('type_cell', fn (Guide $guide) => view('guides.partials.type-cell', compact('guide'))->render())
            ->addColumn('date_cell', fn (Guide $guide) => view('guides.partials.date-cell', compact('guide'))->render())
            ->addColumn('action', fn (Guide $guide) => view('guides.partials.actions', compact('guide'))->render())
            ->orderColumn('title_cell', 'title $1')
            ->orderColumn('type_cell', 'type $1')
            ->orderColumn('date_cell', 'created_at $1')
            ->rawColumns(['title_cell', 'topic_cell', 'type_cell', 'date_cell', 'action'])
            ->only(['title_cell', 'topic_cell', 'type_cell', 'date_cell', 'action'])
            ->toJson();

        // Never let a proxy or the browser replay an old page of rows.
        return $table->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * The questions already linked to a guide, for the edit modal's picker.
     */
    public function questions(int $course, int $chapter, Guide $guide): JsonResponse
    {
        $this->scope($course, $chapter, $guide);

        return response()->json(
            $guide->questionables()->with('question:id,question')->get()
                ->filter(fn ($link) => $link->question)
                ->map(fn ($link) => [
                    'id' => $link->question->id,
                    'text' => $link->question->question,
                ])
                ->values()
        );
    }

    /**
     * Store a newly created guide.
     */
    public function store(Request $request, int $course, int $chapter)
    {
        $this->scope($course, $chapter);

        $data = $this->validated($request);

        $guide = DB::transaction(function () use ($data, $request, $chapter) {
            $guide = Guide::create([
                // The chapter comes from the chain, not from a picker in the form.
                'chapter_id' => $chapter,
                'topic_id' => $data['topic_id'] ?? null,
                'added_by' => $request->user()->id,
                'type' => $data['type'],
                'title' => $data['title'],
                'slug' => $data['slug'],
                'content' => $data['content'],
            ]);

            $this->syncQuestions($guide, $data);

            return $guide;
        });

        return $this->respond($request, $course, $chapter, $guide->fresh(), 'Guide created successfully.', 201);
    }

    /**
     * Update the given guide.
     */
    public function update(Request $request, int $course, int $chapter, Guide $guide)
    {
        $this->scope($course, $chapter, $guide);

        $data = $this->validated($request, $guide);

        // The chapter stays as it is — it belongs to the chain, not the form.
        DB::transaction(function () use ($guide, $data) {
            $guide->update([
                'topic_id' => $data['topic_id'] ?? null,
                'type' => $data['type'],
                'title' => $data['title'],
                'slug' => $data['slug'],
                'content' => $data['content'],
            ]);

            $this->syncQuestions($guide, $data);
        });

        return $this->respond($request, $course, $chapter, $guide->fresh(), 'Guide updated successfully.');
    }

    /**
     * Soft delete the given guide.
     */
    public function destroy(Request $request, int $course, int $chapter, Guide $guide)
    {
        $this->scope($course, $chapter, $guide);

        DB::transaction(function () use ($guide) {
            $guide->questionables()->delete();
            $guide->delete();
        });

        return $this->respond($request, $course, $chapter, null, 'Guide deleted successfully.');
    }

    /**
     * Replaces the guide's question links: existing questions come through as
     * ids, freshly written ones are created in the bank first.
     */
    private function syncQuestions(Guide $guide, array $data): void
    {
        $guide->questionables()->delete();

        $questionIds = collect($data['question_ids'] ?? [])->map(fn ($id) => (int) $id);

        $newQuestions = collect($data['new_questions'] ?? [])
            ->map(fn ($text) => trim($text))
            ->filter();

        if ($newQuestions->isNotEmpty()) {
            $categoryId = QuestionCategory::firstOrCreate(['type' => 'theory'])->id;

            $questionIds = $questionIds->merge(
                $newQuestions->map(fn ($text) => QuestionBank::create([
                    'chapter_id' => $guide->chapter_id,
                    'question_categories_id' => $categoryId,
                    'question' => $text,
                ])->id)
            );
        }

        foreach ($questionIds->unique() as $questionId) {
            $guide->questionables()->create([
                'chapter_id' => $guide->chapter_id,
                'question_id' => $questionId,
            ]);
        }
    }

    /**
     * Shared validation. On update the slug ignores the guide's own row, and a
     * blank slug falls back to one derived from the title.
     */
    private function validated(Request $request, ?Guide $guide = null): array
    {
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('title')),
        ]);

        return $request->validate([
            'topic_id' => ['nullable', 'integer', 'exists:topics,id'],
            'type' => ['required', Rule::in(array_keys(self::TYPES))],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('guides', 'slug')->ignore($guide?->id),
            ],
            'content' => ['required', 'string'],
            'question_ids' => ['nullable', 'array'],
            'question_ids.*' => ['integer', 'exists:question_bank,id'],
            'new_questions' => ['nullable', 'array'],
            'new_questions.*' => ['string', 'max:1000'],
        ]);
    }

    /**
     * JSON for fetch/AJAX callers, a redirect back to the listing for plain
     * form posts.
     */
    private function respond(Request $request, int $course, int $chapter, ?Guide $guide, string $message, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json(array_filter([
                'message' => $message,
                'data' => $guide,
            ], fn ($value) => $value !== null), $status);
        }

        return redirect()->route('guides', [$course, $chapter])->with('success', $message);
    }

    /**
     * Guards the course › chapter › guide chain so a mismatched URL 404s
     * instead of quietly operating on another chapter's guides.
     */
    private function scope(int $course, int $chapter, ?Guide $guide = null): array
    {
        $courseModel = Course::findOrFail($course);
        $chapterModel = Chapter::where('course_id', $courseModel->id)->findOrFail($chapter);

        abort_if($guide && $guide->chapter_id !== $chapterModel->id, 404);

        return [$courseModel, $chapterModel];
    }
}

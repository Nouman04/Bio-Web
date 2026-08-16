<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\QuestionBank;
use App\Models\QuestionCategory;
use App\Models\Summary;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SummaryController extends Controller
{
    /**
     * Display the summaries listing. The grid itself is loaded by DataTables
     * from the `summaries.data` endpoint below.
     */
    public function index(Request $request)
    {
        return view('summaries.index', [
            'chapters' => Chapter::orderBy('chapter_number')->get(['id', 'title']),
            'topics' => Topic::orderBy('title')->get(['id', 'title']),
            'filters' => [
                'title' => $request->input('title', ''),
                'chapter' => $request->input('chapter', ''),
                'topic' => $request->input('topic', ''),
            ],
        ]);
    }

    /**
     * Server-side DataTables source for the summaries list.
     */
    public function data(Request $request): JsonResponse
    {
        $summaries = Summary::query()
            ->with(['chapter:id,title', 'topic:id,title'])
            ->withCount('questionables');

        // Filters from the filter card above the table.
        $summaries->when(
            $request->input('search_term'),
            fn ($query, $title) => $query->where('title', 'like', "%{$title}%")
        );

        $summaries->when($request->input('chapter'), fn ($query, $id) => $query->where('chapter_id', $id));
        $summaries->when($request->input('topic'), fn ($query, $id) => $query->where('topic_id', $id));

        $table = DataTables::eloquent($summaries)
            ->addColumn('title_cell', fn (Summary $summary) => view('summaries.partials.title-cell', compact('summary'))->render())
            ->addColumn('chapter_cell', fn (Summary $summary) => view('summaries.partials.chapter-cell', compact('summary'))->render())
            ->addColumn('topic_cell', fn (Summary $summary) => view('summaries.partials.topic-cell', compact('summary'))->render())
            ->addColumn('questions_cell', fn (Summary $summary) => view('summaries.partials.questions-cell', compact('summary'))->render())
            ->addColumn('action', fn (Summary $summary) => view('summaries.partials.actions', compact('summary'))->render())
            ->orderColumn('title_cell', 'title $1')
            ->orderColumn('questions_cell', 'questionables_count $1')
            ->rawColumns(['title_cell', 'chapter_cell', 'topic_cell', 'questions_cell', 'action'])
            ->only(['title_cell', 'chapter_cell', 'topic_cell', 'questions_cell', 'action'])
            ->toJson();

        // Never let a proxy or the browser replay an old page of rows.
        return $table->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * The questions already linked to a summary, for the edit modal's picker.
     */
    public function questions(Summary $summary): JsonResponse
    {
        return response()->json(
            $summary->questionables()->with('question:id,question')->get()
                ->filter(fn ($link) => $link->question)
                ->map(fn ($link) => [
                    'id' => $link->question->id,
                    'text' => $link->question->question,
                ])
                ->values()
        );
    }

    /**
     * Store a newly created summary.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $summary = DB::transaction(function () use ($data, $request) {
            $summary = Summary::create([
                'chapter_id' => $data['chapter_id'],
                'topic_id' => $data['topic_id'] ?? null,
                'added_by' => $request->user()->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'content' => $data['content'],
            ]);

            $this->syncQuestions($summary, $data);

            return $summary;
        });

        return $this->respond($request, $summary->fresh(), 'Summary created successfully.', 201);
    }

    /**
     * Update the given summary.
     */
    public function update(Request $request, Summary $summary)
    {
        $data = $this->validated($request, $summary);

        DB::transaction(function () use ($summary, $data) {
            $summary->update([
                'chapter_id' => $data['chapter_id'],
                'topic_id' => $data['topic_id'] ?? null,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'content' => $data['content'],
            ]);

            $this->syncQuestions($summary, $data);
        });

        return $this->respond($request, $summary->fresh(), 'Summary updated successfully.');
    }

    /**
     * Soft delete the given summary.
     */
    public function destroy(Request $request, Summary $summary)
    {
        DB::transaction(function () use ($summary) {
            $summary->questionables()->delete();
            $summary->delete();
        });

        return $this->respond($request, null, 'Summary deleted successfully.');
    }

    /**
     * Replaces the summary's question links: existing questions come through as
     * ids, freshly written ones are created in the bank first.
     */
    private function syncQuestions(Summary $summary, array $data): void
    {
        $summary->questionables()->delete();

        $questionIds = collect($data['question_ids'] ?? [])->map(fn ($id) => (int) $id);

        // Newly written questions join the bank under the summary's chapter.
        $newQuestions = collect($data['new_questions'] ?? [])
            ->map(fn ($text) => trim($text))
            ->filter();

        if ($newQuestions->isNotEmpty()) {
            $categoryId = QuestionCategory::firstOrCreate(['type' => 'theory'])->id;

            $questionIds = $questionIds->merge(
                $newQuestions->map(fn ($text) => QuestionBank::create([
                    'chapter_id' => $summary->chapter_id,
                    'question_categories_id' => $categoryId,
                    'question' => $text,
                ])->id)
            );
        }

        foreach ($questionIds->unique() as $questionId) {
            $summary->questionables()->create([
                'chapter_id' => $summary->chapter_id,
                'question_id' => $questionId,
            ]);
        }
    }

    /**
     * Shared validation. On update the slug ignores the summary's own row, and
     * a blank slug falls back to one derived from the title.
     */
    private function validated(Request $request, ?Summary $summary = null): array
    {
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('title')),
        ]);

        return $request->validate([
            'chapter_id' => ['required', 'integer', 'exists:chapters,id'],
            'topic_id' => ['nullable', 'integer', 'exists:topics,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('summaries', 'slug')->ignore($summary?->id),
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
    private function respond(Request $request, ?Summary $summary, string $message, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json(array_filter([
                'message' => $message,
                'data' => $summary,
            ], fn ($value) => $value !== null), $status);
        }

        return redirect()->route('summaries')->with('success', $message);
    }
}

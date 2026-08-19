<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LinksQuestions;
use App\Models\Chapter;
use App\Models\Diagram;
use App\Models\QuestionBank;
use App\Models\QuestionCategory;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ImageController extends Controller
{
    use LinksQuestions;

    /**
     * Display the diagrams listing. The grid itself is loaded by DataTables
     * from the `diagrams.data` endpoint below.
     */
    public function index(Request $request)
    {
        return view('diagrams.index', [
            'chapters' => Chapter::orderBy('chapter_number')->get(['id', 'uuid', 'title']),
            'topics' => Topic::orderBy('title')->get(['id', 'uuid', 'title']),
            'filters' => [
                'title' => $request->input('title', ''),
                'topic' => $request->input('topic', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
            ],
        ]);
    }

    /**
     * Server-side DataTables source for the diagrams list.
     */
    public function data(Request $request): JsonResponse
    {
        $diagrams = Diagram::query()
            ->with(['chapter:id,title', 'topic:id,title'])
            ->withCount('questionables');

        // Filters from the filter card above the table.
        $diagrams->when(
            $request->input('search_term'),
            fn ($query, $title) => $query->where('title', 'like', "%{$title}%")
        );

        $diagrams->when($request->input('topic'), fn ($query, $uuid) => $query->whereRelation('topic', 'uuid', $uuid));
        $diagrams->when($request->input('date_from'), fn ($query, $date) => $query->whereDate('created_at', '>=', $date));
        $diagrams->when($request->input('date_to'), fn ($query, $date) => $query->whereDate('created_at', '<=', $date));

        $table = DataTables::eloquent($diagrams)
            ->addColumn('preview_cell', fn (Diagram $diagram) => view('diagrams.partials.preview-cell', compact('diagram'))->render())
            ->addColumn('title_cell', fn (Diagram $diagram) => view('diagrams.partials.title-cell', compact('diagram'))->render())
            ->addColumn('chapter_cell', fn (Diagram $diagram) => view('diagrams.partials.chapter-cell', compact('diagram'))->render())
            ->addColumn('topic_cell', fn (Diagram $diagram) => view('diagrams.partials.topic-cell', compact('diagram'))->render())
            ->addColumn('date_cell', fn (Diagram $diagram) => view('diagrams.partials.date-cell', compact('diagram'))->render())
            ->addColumn('action', fn (Diagram $diagram) => view('diagrams.partials.actions', compact('diagram'))->render())
            ->orderColumn('title_cell', 'title $1')
            ->orderColumn('date_cell', 'created_at $1')
            ->rawColumns(['preview_cell', 'title_cell', 'chapter_cell', 'topic_cell', 'date_cell', 'action'])
            ->only(['preview_cell', 'title_cell', 'chapter_cell', 'topic_cell', 'date_cell', 'action'])
            ->toJson();

        // Never let a proxy or the browser replay an old page of rows.
        return $table->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * The questions already linked to a diagram, for the edit modal's picker.
     */
    public function questions(Diagram $diagram): JsonResponse
    {
        return response()->json(
            $diagram->questionables()->with('question:id,question')->get()
                ->filter(fn ($link) => $link->question)
                ->map(fn ($link) => [
                    'id' => $link->question->id,
                    'text' => $link->question->question,
                ])
                ->values()
        );
    }

    /**
     * Store a newly created diagram.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $this->validateNewQuestions($data);

        $diagram = DB::transaction(function () use ($data, $request) {
            $diagram = Diagram::create([
                'chapter_id' => $data['chapter_id'] ?? null,
                'topic_id' => $data['topic_id'] ?? null,
                'added_by' => $request->user()->id,
                'image_path' => $request->file('image')->store('diagrams', 'public'),
                'title' => $data['title'],
                'slug' => $data['slug'],
                'content' => $data['content'] ?? null,
            ]);

            $this->syncQuestionLinks($diagram, $data);

            return $diagram;
        });

        return $this->respond($request, $diagram->fresh(), 'Diagram created successfully.', 201);
    }

    /**
     * Update the given diagram. The image is only replaced when a new file is
     * actually uploaded.
     */
    public function update(Request $request, Diagram $diagram)
    {
        $data = $this->validated($request, $diagram);

        $this->validateNewQuestions($data);

        DB::transaction(function () use ($request, $diagram, $data) {
            $attributes = [
                'chapter_id' => $data['chapter_id'] ?? null,
                'topic_id' => $data['topic_id'] ?? null,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'content' => $data['content'] ?? null,
            ];

            if ($request->hasFile('image')) {
                Storage::disk('public')->delete($diagram->image_path);
                $attributes['image_path'] = $request->file('image')->store('diagrams', 'public');
            }

            $diagram->update($attributes);
            $this->syncQuestionLinks($diagram, $data);
        });

        return $this->respond($request, $diagram->fresh(), 'Diagram updated successfully.');
    }

    /**
     * Soft delete the given diagram. The file is kept so the row stays
     * restorable.
     */
    public function destroy(Request $request, Diagram $diagram)
    {
        DB::transaction(function () use ($diagram) {
            $diagram->questionables()->delete();
            $diagram->delete();
        });

        return $this->respond($request, null, 'Diagram deleted successfully.');
    }


    /**
     * Shared validation. The modal posts the body copy as `description`, and a
     * blank slug falls back to one derived from the title. The image is only
     * required when creating.
     */
    private function validated(Request $request, ?Diagram $diagram = null): array
    {
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('title')),
            'content' => $request->input('content', $request->input('description')),
        ]);

        return $request->validate([
            'chapter_id' => ['nullable', 'integer', 'exists:chapters,id'],
            'topic_id' => ['nullable', 'integer', 'exists:topics,id'],
            'image' => [$diagram ? 'nullable' : 'required', 'image', 'max:10240'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('diagrams', 'slug')->ignore($diagram?->id),
            ],
            'content' => ['nullable', 'string'],
        ] + $this->questionLinkRules());
    }

    /**
     * JSON for fetch/AJAX callers, a redirect back to the listing for plain
     * form posts.
     */
    private function respond(Request $request, ?Diagram $diagram, string $message, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json(array_filter([
                'message' => $message,
                'data' => $diagram,
            ], fn ($value) => $value !== null), $status);
        }

        return redirect()->route('diagrams')->with('success', $message);
    }
}

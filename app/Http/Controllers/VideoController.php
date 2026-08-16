<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\QuestionBank;
use App\Models\QuestionCategory;
use App\Models\Topic;
use App\Models\VideoLesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class VideoController extends Controller
{
    /**
     * Display the video lessons listing. The grid itself is loaded by
     * DataTables from the `videos.data` endpoint below.
     */
    public function index(Request $request)
    {
        return view('videos.index', [
            'chapters' => Chapter::orderBy('chapter_number')->get(['id', 'title']),
            'topics' => Topic::orderBy('title')->get(['id', 'title']),
            'filters' => [
                'title' => $request->input('title', ''),
                'topic' => $request->input('topic', ''),
                'source' => $request->input('source', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
            ],
        ]);
    }

    /**
     * Server-side DataTables source for the video lessons list.
     */
    public function data(Request $request): JsonResponse
    {
        $videos = VideoLesson::query()
            ->with(['chapter:id,title', 'topic:id,title'])
            ->withCount('questionables');

        // Filters from the filter card above the table.
        $videos->when(
            $request->input('search_term'),
            fn ($query, $title) => $query->where('title', 'like', "%{$title}%")
        );

        $videos->when($request->input('topic'), fn ($query, $id) => $query->where('topic_id', $id));
        $videos->when($request->input('date_from'), fn ($query, $date) => $query->whereDate('created_at', '>=', $date));
        $videos->when($request->input('date_to'), fn ($query, $date) => $query->whereDate('created_at', '<=', $date));

        // Uploaded file vs. external link.
        $videos->when($request->input('source'), function ($query, $source) {
            $source === 'uploaded'
                ? $query->whereNotNull('file_path')->where('file_path', '!=', '')
                : $query->where(fn ($q) => $q->whereNull('file_path')->orWhere('file_path', ''));
        });

        $table = DataTables::eloquent($videos)
            ->addColumn('title_cell', fn (VideoLesson $video) => view('videos.partials.title-cell', compact('video'))->render())
            ->addColumn('source_cell', fn (VideoLesson $video) => view('videos.partials.source-cell', compact('video'))->render())
            ->addColumn('chapter_cell', fn (VideoLesson $video) => view('videos.partials.chapter-cell', compact('video'))->render())
            ->addColumn('topic_cell', fn (VideoLesson $video) => view('videos.partials.topic-cell', compact('video'))->render())
            ->addColumn('date_cell', fn (VideoLesson $video) => view('videos.partials.date-cell', compact('video'))->render())
            ->addColumn('action', fn (VideoLesson $video) => view('videos.partials.actions', compact('video'))->render())
            ->orderColumn('title_cell', 'title $1')
            ->orderColumn('date_cell', 'created_at $1')
            ->rawColumns(['title_cell', 'source_cell', 'chapter_cell', 'topic_cell', 'date_cell', 'action'])
            ->only(['title_cell', 'source_cell', 'chapter_cell', 'topic_cell', 'date_cell', 'action'])
            ->toJson();

        // Never let a proxy or the browser replay an old page of rows.
        return $table->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * The questions already linked to a lesson, for the edit modal's picker.
     */
    public function questions(VideoLesson $video): JsonResponse
    {
        return response()->json(
            $video->questionables()->with('question:id,question')->get()
                ->filter(fn ($link) => $link->question)
                ->map(fn ($link) => [
                    'id' => $link->question->id,
                    'text' => $link->question->question,
                ])
                ->values()
        );
    }

    /**
     * Store a newly created video lesson.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $video = DB::transaction(function () use ($data, $request) {
            $video = VideoLesson::create([
                'chapter_id' => $data['chapter_id'] ?? null,
                'topic_id' => $data['topic_id'] ?? null,
                'added_by' => $request->user()->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'file_path' => $request->hasFile('video_file')
                    ? $request->file('video_file')->store('videos', 'public')
                    : null,
                'external_link' => $data['external_link'] ?? null,
            ]);

            $this->syncQuestions($video, $data);

            return $video;
        });

        return $this->respond($request, $video->fresh(), 'Video lesson created successfully.', 201);
    }

    /**
     * Update the given lesson. The file is only replaced when a new one is
     * actually uploaded.
     */
    public function update(Request $request, VideoLesson $video)
    {
        $data = $this->validated($request, $video);

        DB::transaction(function () use ($request, $video, $data) {
            $attributes = [
                'chapter_id' => $data['chapter_id'] ?? null,
                'topic_id' => $data['topic_id'] ?? null,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'external_link' => $data['external_link'] ?? null,
            ];

            if ($request->hasFile('video_file')) {
                if ($video->file_path) {
                    Storage::disk('public')->delete($video->file_path);
                }
                $attributes['file_path'] = $request->file('video_file')->store('videos', 'public');
            }

            $video->update($attributes);
            $this->syncQuestions($video, $data);
        });

        return $this->respond($request, $video->fresh(), 'Video lesson updated successfully.');
    }

    /**
     * Soft delete the given lesson. The file is kept so the row stays
     * restorable.
     */
    public function destroy(Request $request, VideoLesson $video)
    {
        DB::transaction(function () use ($video) {
            $video->questionables()->delete();
            $video->delete();
        });

        return $this->respond($request, null, 'Video lesson deleted successfully.');
    }

    /**
     * Replaces the lesson's question links: existing questions come through as
     * ids, freshly written ones are created in the bank first.
     */
    private function syncQuestions(VideoLesson $video, array $data): void
    {
        $video->questionables()->delete();

        $questionIds = collect($data['question_ids'] ?? [])->map(fn ($id) => (int) $id);

        $newQuestions = collect($data['new_questions'] ?? [])
            ->map(fn ($text) => trim($text))
            ->filter();

        if ($newQuestions->isNotEmpty()) {
            $categoryId = QuestionCategory::firstOrCreate(['type' => 'theory'])->id;

            $questionIds = $questionIds->merge(
                $newQuestions->map(fn ($text) => QuestionBank::create([
                    'chapter_id' => $video->chapter_id,
                    'question_categories_id' => $categoryId,
                    'question' => $text,
                ])->id)
            );
        }

        foreach ($questionIds->unique() as $questionId) {
            $video->questionables()->create([
                'chapter_id' => $video->chapter_id,
                'question_id' => $questionId,
            ]);
        }
    }

    /**
     * Shared validation. A lesson needs either an uploaded file or an external
     * link; on update the existing file counts, so neither is required.
     */
    private function validated(Request $request, ?VideoLesson $video = null): array
    {
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('title')),
        ]);

        $hasStoredVideo = $video && ($video->file_path || $video->external_link);

        return $request->validate([
            'chapter_id' => ['nullable', 'integer', 'exists:chapters,id'],
            'topic_id' => ['nullable', 'integer', 'exists:topics,id'],
            'video_file' => [
                $hasStoredVideo ? 'nullable' : 'required_without:external_link',
                'file',
                'mimetypes:video/mp4,video/webm,video/ogg,video/quicktime',
                'max:512000',
            ],
            'external_link' => [
                $hasStoredVideo ? 'nullable' : 'required_without:video_file',
                'nullable',
                'url',
                'max:2048',
            ],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('video_lessons', 'slug')->ignore($video?->id),
            ],
            'description' => ['nullable', 'string'],
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
    private function respond(Request $request, ?VideoLesson $video, string $message, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json(array_filter([
                'message' => $message,
                'data' => $video,
            ], fn ($value) => $value !== null), $status);
        }

        return redirect()->route('videos')->with('success', $message);
    }
}

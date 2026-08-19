<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LinksQuestions;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\QuestionBank;
use App\Models\QuestionCategory;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TopicController extends Controller
{
    use LinksQuestions;

    /**
     * Topics belong to a chapter, which belongs to a course, so every action
     * here is reached through course › chapter › topic. The chapter comes from
     * the URL rather than a picker in the form.
     */
    public function index(Request $request, Course $course, Chapter $chapter)
    {
        [$courseModel, $chapterModel] = $this->scope($course, $chapter);

        return view('topics.index', [
            'course' => $courseModel,
            'chapter' => $chapterModel,
            'filters' => [
                'title' => $request->input('title', ''),
            ],
        ]);
    }

    /**
     * Server-side DataTables source for the topics of one chapter.
     */
    public function data(Request $request, Course $course, Chapter $chapter): JsonResponse
    {
        $this->scope($course, $chapter);

        $topics = Topic::query()
            ->where('chapter_id', $chapter->id)
            ->withCount(['questionables', 'attachments']);

        // Filter from the filter card above the table.
        $topics->when(
            $request->input('search_term'),
            fn ($query, $title) => $query->where('title', 'like', "%{$title}%")
        );

        $table = DataTables::eloquent($topics)
            ->addColumn('title_cell', fn (Topic $topic) => view('topics.partials.title-cell', compact('topic'))->render())
            ->addColumn('questions_cell', fn (Topic $topic) => view('topics.partials.questions-cell', compact('topic'))->render())
            ->addColumn('action', fn (Topic $topic) => view('topics.partials.actions', [
                'topic' => $topic,
                'courseId' => $course,
                'chapterId' => $chapter,
            ])->render())
            ->orderColumn('title_cell', 'title $1')
            ->orderColumn('questions_cell', 'questionables_count $1')
            ->rawColumns(['title_cell', 'questions_cell', 'action'])
            ->only(['title_cell', 'questions_cell', 'action'])
            ->toJson();

        // Never let a proxy or the browser replay an old page of rows.
        return $table->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * The questions already linked to a topic, for the edit modal's picker.
     */
    public function questions(Course $course, Chapter $chapter, Topic $topic): JsonResponse
    {
        $this->scope($course, $chapter, $topic);

        return response()->json(
            $topic->questionables()->with('question:id,question')->get()
                ->filter(fn ($link) => $link->question)
                ->map(fn ($link) => [
                    'id' => $link->question->id,
                    'text' => $link->question->question,
                ])
                ->values()
        );
    }

    /**
     * Store a newly created topic under the chapter from the URL.
     */
    public function store(Request $request, Course $course, Chapter $chapter)
    {
        $this->scope($course, $chapter);

        $data = $this->validated($request);

        $this->validateNewQuestions($data);

        $topic = DB::transaction(function () use ($data, $request, $chapter) {
            $topic = Topic::create([
                'chapter_id' => $chapter,
                'title' => $data['title'],
                'content' => $data['content'] ?? null,
            ]);

            $this->syncQuestionLinks($topic, $data);
            $this->storeAttachments($topic, $request);

            return $topic;
        });

        return $this->respond($request, $course, $chapter, $topic->fresh(), 'Topic created successfully.', 201);
    }

    /**
     * Update the given topic. Newly uploaded files are added to the existing
     * attachments rather than replacing them.
     */
    public function update(Request $request, Course $course, Chapter $chapter, Topic $topic)
    {
        $this->scope($course, $chapter, $topic);

        $data = $this->validated($request);

        $this->validateNewQuestions($data);

        DB::transaction(function () use ($request, $topic, $data) {
            $topic->update([
                'title' => $data['title'],
                'content' => $data['content'] ?? null,
            ]);

            $this->syncQuestionLinks($topic, $data);
            $this->storeAttachments($topic, $request);
        });

        return $this->respond($request, $course, $chapter, $topic->fresh(), 'Topic updated successfully.');
    }

    /**
     * Soft delete the given topic.
     */
    public function destroy(Request $request, Course $course, Chapter $chapter, Topic $topic)
    {
        $this->scope($course, $chapter, $topic);

        DB::transaction(function () use ($topic) {
            $topic->questionables()->delete();
            $topic->delete();
        });

        return $this->respond($request, $course, $chapter, null, 'Topic deleted successfully.');
    }

    /**
     * Display the question assignment page for a specific topic.
     */
    public function assign(Course $course, Chapter $chapter, Topic $topic)
    {
        [$courseModel, $chapterModel] = $this->scope($course, $chapter, $topic);

        return view('topics.assign', [
            'course' => $courseModel,
            'chapter' => $chapterModel,
            'topicId' => $topic->id,
            'topicName' => $topic->title,
            'questions' => QuestionBank::latest('id')->limit(25)->get()
                ->map(fn (QuestionBank $question) => [
                    'id' => $question->id,
                    'text' => $question->question,
                    'type' => $question->category?->type === 'mcqs' ? 'MCQ' : 'Theory',
                    'difficulty' => $question->difficulty_level ?: '—',
                ]),
        ]);
    }

    /**
     * Guards the course › chapter › topic chain so a mismatched URL 404s
     * instead of quietly operating on someone else's records.
     */
    private function scope(Course $course, Chapter $chapter, ?Topic $topic = null): array
    {
        $courseModel = $course;
        $chapterModel = $chapter;

        abort_if($chapterModel->course_id !== $courseModel->id, 404);

        abort_if($topic && $topic->chapter_id !== $chapterModel->id, 404);

        return [$courseModel, $chapterModel];
    }

    /**
     * Files posted with the form are stored and attached to the topic.
     */
    private function storeAttachments(Topic $topic, Request $request): void
    {
        foreach ($request->file('attachments', []) as $file) {
            $topic->attachments()->create([
                'file_path' => $file->store('topics', 'public'),
            ]);
        }
    }


    /**
     * Shared validation. The chapter is not validated here — it comes from the
     * URL and is checked by scope().
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ] + $this->questionLinkRules());
    }

    /**
     * JSON for fetch/AJAX callers, a redirect back to the listing for plain
     * form posts.
     */
    private function respond(Request $request, Course $course, Chapter $chapter, ?Topic $topic, string $message, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json(array_filter([
                'message' => $message,
                'data' => $topic,
            ], fn ($value) => $value !== null), $status);
        }

        return redirect()->route('topics', [$course, $chapter])->with('success', $message);
    }
}

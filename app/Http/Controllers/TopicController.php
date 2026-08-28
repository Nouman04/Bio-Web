<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesChapterChain;
use App\Http\Requests\Topic\StoreTopicRequest;
use App\Http\Requests\Topic\UpdateTopicRequest;
use App\Http\Resources\TopicOptionResource;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\QuestionBank;
use App\Models\Topic;
use App\Services\TopicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

/**
 * Topics belong to a chapter, which belongs to a course, so every action here
 * is reached through course › chapter › topic. The chapter comes from the URL
 * rather than a picker in the form.
 *
 * This decides what the request asked for and what to send back; TopicService
 * decides what it means for the database.
 */
class TopicController extends Controller
{
    use ScopesChapterChain;

    public function __construct(private readonly TopicService $topics)
    {
    }

    public function index(Request $request, Course $course, Chapter $chapter)
    {
        $this->scope($course, $chapter);

        return view('topics.index', [
            'course' => $course,
            'chapter' => $chapter,
            'filters' => [
                'title' => $request->input('title', ''),
            ],
        ]);
    }

    /**
     * Server-side DataTables source for the topics of one chapter.
     *
     * DataTables owns this response shape — it renders Blade partials into
     * cells rather than returning models — so an API resource has nothing to
     * describe here.
     */
    public function data(Request $request, Course $course, Chapter $chapter): JsonResponse
    {
        $this->scope($course, $chapter);

        $table = DataTables::eloquent($this->topics->listing($chapter, $request->input('search_term')))
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
     * Type-ahead for the parent topic picker, across every chapter.
     */
    public function search(Request $request): JsonResponse
    {
        return TopicOptionResource::collection(
            $this->topics->search($request->input('q'), $request->input('exclude'))
        )->response();
    }

    /**
     * The questions already linked to a topic, for the edit modal's picker.
     */
    public function questions(Course $course, Chapter $chapter, Topic $topic): JsonResponse
    {
        $this->scope($course, $chapter, $topic);

        return response()->json($this->topics->pickerQuestions($topic));
    }

    public function store(StoreTopicRequest $request, Course $course, Chapter $chapter)
    {
        $this->scope($course, $chapter);

        $topic = $this->topics->create($chapter, $request->validated(), $request->file('attachments', []));

        return $this->respond($request, $course, $chapter, $topic, 'Topic created successfully.', 201);
    }

    public function update(UpdateTopicRequest $request, Course $course, Chapter $chapter, Topic $topic)
    {
        $this->scope($course, $chapter, $topic);

        $topic = $this->topics->update($topic, $request->validated(), $request->file('attachments', []));

        return $this->respond($request, $course, $chapter, $topic, 'Topic updated successfully.');
    }

    public function destroy(Request $request, Course $course, Chapter $chapter, Topic $topic)
    {
        $this->scope($course, $chapter, $topic);

        $this->topics->delete($topic);

        return $this->respond($request, $course, $chapter, null, 'Topic deleted successfully.');
    }

    /**
     * Read-only detail page for one topic.
     */
    public function show(Course $course, Chapter $chapter, Topic $topic)
    {
        $this->scope($course, $chapter, $topic);

        return view('topics.show', [
            'course' => $course,
            'chapter' => $chapter,
            'topic' => $this->topics->forDetail($topic),
            'questions' => $this->topics->questions($topic),
        ]);
    }

    /**
     * The question assignment page for one topic.
     */
    public function assign(Course $course, Chapter $chapter, Topic $topic)
    {
        $this->scope($course, $chapter, $topic);

        return view('topics.assign', [
            'course' => $course,
            'chapter' => $chapter,
            'topicId' => $topic->id,
            'topicName' => $topic->title,
            'questions' => QuestionBank::with('category:id,type')->latest('id')->limit(25)->get()
                ->map(fn (QuestionBank $question) => [
                    'id' => $question->id,
                    'text' => $question->question,
                    'type' => $question->category?->type === 'mcqs' ? 'MCQ' : 'Theory',
                    'difficulty' => $question->difficulty_level ?: '—',
                ]),
        ]);
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

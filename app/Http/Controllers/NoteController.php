<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LinksQuestions;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Note;
use App\Models\Summary;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class NoteController extends Controller
{
    use LinksQuestions;

    /**
     * Note types, keyed by the enum value stored on the row.
     */
    public const TYPES = [
        'exam_notes' => 'Exam Notes',
        'summary' => 'Summary Notes',
    ];

    /**
     * Notes belong to a chapter, so every action here is reached through
     * course › chapter › note. The chapter comes from the URL rather than a
     * picker in the form.
     */
    public function index(Request $request, Course $course, Chapter $chapter)
    {
        [$courseModel, $chapterModel] = $this->scope($course, $chapter);

        return view('notes.index', [
            'course' => $courseModel,
            'chapter' => $chapterModel,
            'topics' => Topic::where('chapter_id', $chapterModel->id)->orderBy('title')->get(['id', 'uuid', 'title']),
            // Summary notes are written from one of this chapter's summaries.
            'summaries' => Summary::where('chapter_id', $chapterModel->id)->orderBy('title')->get(['id', 'uuid', 'title']),
            'types' => self::TYPES,
            'filters' => [
                'title' => $request->input('title', ''),
                'topic' => $request->input('topic', ''),
                'type' => $request->input('type', ''),
            ],
        ]);
    }

    /**
     * Server-side DataTables source for the notes of one chapter.
     */
    public function data(Request $request, Course $course, Chapter $chapter): JsonResponse
    {
        $this->scope($course, $chapter);

        $notes = Note::query()
            ->where('chapter_id', $chapter->id)
            ->with(['topic:id,title', 'summary:id,title']);

        // Filters from the filter card above the table.
        $notes->when($request->input('search_term'), function ($query, $term) {
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('content', 'like', "%{$term}%");
            });
        });

        $notes->when($request->input('topic'), fn ($query, $uuid) => $query->whereRelation('topic', 'uuid', $uuid));
        $notes->when($request->input('type'), fn ($query, $type) => $query->where('type', $type));

        $table = DataTables::eloquent($notes)
            ->addColumn('title_cell', fn (Note $note) => view('notes.partials.title-cell', compact('note'))->render())
            ->addColumn('topic_cell', fn (Note $note) => view('notes.partials.topic-cell', compact('note'))->render())
            ->addColumn('type_cell', fn (Note $note) => view('notes.partials.type-cell', compact('note'))->render())
            ->addColumn('date_cell', fn (Note $note) => view('notes.partials.date-cell', compact('note'))->render())
            ->addColumn('action', fn (Note $note) => view('notes.partials.actions', [
                'note' => $note,
                'courseId' => $course,
                'chapterId' => $chapter,
            ])->render())
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
     * The questions already linked to a note, for the edit modal's picker.
     */
    public function questions(Course $course, Chapter $chapter, Note $note): JsonResponse
    {
        $this->scope($course, $chapter, $note);

        return response()->json(
            $note->questionables()->with('question:id,question')->get()
                ->filter(fn ($link) => $link->question)
                ->map(fn ($link) => [
                    'id' => $link->question->id,
                    'text' => $link->question->plain_question,
                ])
                ->values()
        );
    }

    /**
     * Read-only detail page for one note.
     */
    public function show(Course $course, Chapter $chapter, Note $note)
    {
        [$courseModel, $chapterModel] = $this->scope($course, $chapter, $note);

        return view('notes.show', [
            'course' => $courseModel,
            'chapter' => $chapterModel,
            'note' => $note->load('topic:id,title', 'summary:id,title', 'attachments'),
            'questions' => $this->linkedQuestions($note),
        ]);
    }

    /**
     * Store a newly created note under the chapter from the URL.
     */
    public function store(Request $request, Course $course, Chapter $chapter)
    {
        $this->scope($course, $chapter);

        $data = $this->validated($request, $chapter);

        $this->validateNewQuestions($data);

        $note = DB::transaction(function () use ($data, $chapter) {
            $note = Note::create([
                // The chapter comes from the chain, not from a picker in the form.
                'chapter_id' => $chapter->id,
                'topic_id' => $data['topic_id'] ?? null,
                // Only summary notes carry a summary.
                'summary_id' => $data['type'] === 'summary' ? ($data['summary_id'] ?? null) : null,
                'title' => $data['title'],
                'type' => $data['type'],
                'content' => $data['content'],
            ]);

            $this->syncQuestionLinks($note, $data);

            return $note;
        });

        return $this->respond($request, $course, $chapter, $note->fresh(), 'Note created successfully.', 201);
    }

    /**
     * Update the given note.
     */
    public function update(Request $request, Course $course, Chapter $chapter, Note $note)
    {
        $this->scope($course, $chapter, $note);

        $data = $this->validated($request, $chapter);

        $this->validateNewQuestions($data);

        DB::transaction(function () use ($note, $data) {
            // The chapter stays as it is — it belongs to the chain, not the form.
            $note->update([
                'topic_id' => $data['topic_id'] ?? null,
                // Switching away from a summary note clears the link.
                'summary_id' => $data['type'] === 'summary' ? ($data['summary_id'] ?? null) : null,
                'title' => $data['title'],
                'type' => $data['type'],
                'content' => $data['content'],
            ]);

            $this->syncQuestionLinks($note, $data);
        });

        return $this->respond($request, $course, $chapter, $note->fresh(), 'Note updated successfully.');
    }

    /**
     * Soft delete the given note.
     */
    public function destroy(Request $request, Course $course, Chapter $chapter, Note $note)
    {
        $this->scope($course, $chapter, $note);

        DB::transaction(function () use ($note) {
            $note->questionables()->delete();
            $note->delete();
        });

        return $this->respond($request, $course, $chapter, null, 'Note deleted successfully.');
    }

    /**
     * Shared validation. The chapter is not validated here — it comes from the
     * URL and is checked by scope().
     */
    private function validated(Request $request, Chapter $chapter): array
    {
        // An untouched select posts "", which would reach the integer columns
        // as an empty string rather than null.
        $request->merge([
            'topic_id' => $request->input('topic_id') ?: null,
            'summary_id' => $request->input('summary_id') ?: null,
        ]);

        return $request->validate([
            // Optional, but when picked it must be a topic in this chapter.
            'topic_id' => [
                'nullable',
                'integer',
                Rule::exists('topics', 'id')->where('chapter_id', $chapter->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(self::TYPES))],
            // Optional even on summary notes, but when one is picked it must
            // be a summary that belongs to this chapter.
            'summary_id' => [
                'nullable',
                'integer',
                Rule::exists('summaries', 'id')->where('chapter_id', $chapter->id),
            ],
            'content' => ['required', 'string'],
        ] + $this->questionLinkRules());
    }

    /**
     * Guards the course › chapter › note chain so a mismatched URL 404s
     * instead of quietly operating on another chapter's notes.
     */
    private function scope(Course $course, Chapter $chapter, ?Note $note = null): array
    {
        $courseModel = $course;
        $chapterModel = $chapter;

        abort_if($chapterModel->course_id !== $courseModel->id, 404);

        abort_if($note && $note->chapter_id !== $chapterModel->id, 404);

        return [$courseModel, $chapterModel];
    }

    /**
     * JSON for fetch/AJAX callers, a redirect back to the listing for plain
     * form posts.
     */
    private function respond(Request $request, Course $course, Chapter $chapter, ?Note $note, string $message, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json(array_filter([
                'message' => $message,
                'data' => $note,
            ], fn ($value) => $value !== null), $status);
        }

        return redirect()->route('notes', [$course, $chapter])->with('success', $message);
    }
}

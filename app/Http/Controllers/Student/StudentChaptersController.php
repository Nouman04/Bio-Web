<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiagramResource;
use App\Http\Resources\FlashcardDeckResource;
use App\Http\Resources\GuideResource;
use App\Http\Resources\NoteResource;
use App\Http\Resources\SummaryResource;
use App\Http\Resources\VideoLessonResource;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Diagram;
use App\Models\Flashcard;
use App\Models\Guide;
use App\Models\Note;
use App\Models\QuestionBank;
use App\Models\Summary;
use App\Models\VideoLesson;
use App\Services\ProgressService;
use App\Services\StripeService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class StudentChaptersController extends Controller
{
    public function __construct(
        private readonly ProgressService $progress,
        private readonly StripeService $stripe,
    ) {
    }

    /**
     * A course's chapters, with how far the student has got in each.
     *
     * A private chapter is listed but locked unless they subscribe — the same
     * rule the public catalogue uses, so the two never disagree.
     */
    public function index(Request $request, $courseId)
    {
        $course = Course::where('uuid', $courseId)->firstOrFail();
        $user = $request->user();

        $subscribed = $this->stripe->subscribedTo($user, $course);
        $chapters = $course->chapters()->orderBy('chapter_number')->get();
        $breakdown = $this->progress->chapterBreakdown($user, $course);

        // Everything the row needs, worked out once here rather than in Blade.
        $rows = $chapters->map(function (Chapter $chapter) use ($breakdown, $subscribed) {
            $totals = $breakdown[$chapter->id] ?? ['progress' => 0.0, 'completed_weight' => 0, 'total_weight' => 0];
            $locked = ! $subscribed && $chapter->visibility !== 'public';

            return [
                'chapter' => $chapter,
                'locked' => $locked,
                'progress' => (float) $totals['progress'],
                'completed_weight' => $totals['completed_weight'],
                'total_weight' => $totals['total_weight'],
                'completed' => ! $locked && $totals['total_weight'] > 0 && $totals['progress'] >= 100,
            ];
        });

        // "Current" is the first chapter they can open and have not finished.
        $current = $rows->first(fn (array $row) => ! $row['locked'] && ! $row['completed']);

        return view('student.chapters.index', [
            'course' => $course,
            'courseId' => $courseId,
            'rows' => $rows,
            'subscribed' => $subscribed,
            'currentId' => $current['chapter']->id ?? null,
            'completedCount' => $rows->where('completed', true)->count(),
            'courseProgress' => $this->progress->courseProgress($user, $course),
        ]);
    }

    /**
     * One chapter's dashboard: its video, its resources and its assessments.
     */
    public function show(Request $request, $courseId, $chapterId)
    {
        [$course, $chapter] = $this->locate($request, $courseId, $chapterId);

        $user = $request->user();
        $siblings = $course->chapters()->orderBy('chapter_number')->get();
        $position = $siblings->search(fn (Chapter $c) => $c->id === $chapter->id);

        return view('student.chapters.show', [
            'course' => $course,
            'chapter' => $chapter,
            'courseId' => $courseId,
            'chapterId' => $chapterId,
            'progress' => $this->progress->chapterProgress($user, $chapter),

            // The lesson the player opens on, and the rest of the tab panels.
            'featured' => $chapter->videoLessons()->orderBy('id')->first(),
            'notes' => $chapter->notes()->latest('id')->take(4)->get(),
            'flashcards' => $chapter->flashcards()->withCount('assessments')->latest('id')->take(4)->get(),
            'diagrams' => $chapter->diagrams()->latest('id')->take(4)->get(),
            'summaries' => $chapter->summaries()->latest('id')->take(4)->get(),
            'guides' => $chapter->guides()->latest('id')->take(4)->get(),
            'videos' => $chapter->videoLessons()->latest('id')->take(4)->get(),
            'quizzes' => $chapter->quizzes()->withCount('questions')->latest('quizzes.id')->take(4)->get(),

            // The outline rail either side of where they are.
            'previous' => $position > 0 ? $siblings[$position - 1] : null,
            'next' => $siblings[$position + 1] ?? null,
        ]);
    }

    /**
     * Resolves the pair from the URL.
     *
     * The paywall is the `subscribed` middleware on the route group; this only
     * has to prove the chapter really belongs to the course.
     *
     * @return array{0: Course, 1: Chapter}
     */
    private function locate(Request $request, string $courseId, string $chapterId): array
    {
        $course = Course::where('uuid', $courseId)->firstOrFail();
        $chapter = Chapter::where('uuid', $chapterId)->firstOrFail();

        abort_if($chapter->course_id !== $course->id, 404);


        return [$course, $chapter];
    }

    /**
     * Every study note in a chapter, searchable and filterable by kind.
     */
    public function notes(Request $request, $courseId, $chapterId)
    {
        $crumbs = $this->crumbs($request, $courseId, $chapterId);
        $search = trim((string) $request->input('search'));
        $type = $request->input('type');

        $notes = $crumbs['chapter']->notes()
            ->with('topic:id,title')
            ->when($search, fn ($query) => $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%")))
            ->when(in_array($type, Note::TYPES, true), fn ($query) => $query->where('type', $type))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        // Read before the collection is resolved to arrays: forView()
        // maps the paginator in place.
        $state = $this->progress->completionFor($request->user(), $notes->getCollection());

        return view('student.chapters.notes.index', $crumbs + [
            'notes' => NoteResource::forView($notes),
            'search' => $search,
            'type' => $type,
            // Which of these the student has already read.
            'state' => $state,
        ]);
    }

    /**
     * One study note, with a contents rail built from its own headings.
     */
    public function showNote(Request $request, $courseId, $chapterId, $noteId)
    {
        $crumbs = $this->crumbs($request, $courseId, $chapterId);
        $chapter = $crumbs['chapter'];

        $note = Note::where('uuid', $noteId)
            ->with(['topic:id,title', 'summary:id,uuid,title', 'attachments', 'flashcards'])
            ->firstOrFail();

        abort_if($note->chapter_id !== $chapter->id, 404);

        $siblings = $chapter->notes()->orderByDesc('id')->get(['id', 'uuid', 'title']);
        $position = $siblings->search(fn ($item) => $item->id === $note->id);

        [$content, $headings] = $this->withAnchors((string) $note->content);

        return view('student.chapters.notes.show', $crumbs + [
            'note' => $note,
            'content' => $content,
            'headings' => $headings,
            'previous' => $position > 0 ? $siblings[$position - 1] : null,
            'next' => $siblings[$position + 1] ?? null,
        ]);
    }

    /**
     * Gives every heading in a body of HTML an id, and returns the list of them
     * so a page can offer a contents rail that actually jumps somewhere.
     *
     * @return array{0: string, 1: array<int, array{id:string, text:string, level:int}>}
     */
    private function withAnchors(string $html): array
    {
        if (trim($html) === '') {
            return ['', []];
        }

        $document = new \DOMDocument();

        // Quill fragments have no wrapper and may hold UTF-8; the meta tag makes
        // the parser read them as such. Malformed markup is not our problem to
        // report, so warnings are suppressed.
        libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="note-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $headings = [];
        $index = 0;

        foreach (['h1', 'h2', 'h3'] as $tag) {
            foreach (iterator_to_array($document->getElementsByTagName($tag)) as $node) {
                $text = trim($node->textContent);

                if ($text === '') {
                    continue;
                }

                $id = 'section-' . (++$index);
                $node->setAttribute('id', $id);

                $headings[] = ['id' => $id, 'text' => $text, 'level' => (int) substr($tag, 1)];
            }
        }

        $root = $document->getElementById('note-root');
        $inner = '';

        foreach ($root?->childNodes ?? [] as $child) {
            $inner .= $document->saveHTML($child);
        }

        // Headings were gathered per tag, so put them back in document order.
        usort($headings, fn ($a, $b) => strpos($inner, 'id="' . $a['id'] . '"') <=> strpos($inner, 'id="' . $b['id'] . '"'));

        return [$inner, $headings];
    }

    /**
     * The chapter's flashcard decks.
     */
    public function flashcards(Request $request, $courseId, $chapterId)
    {
        $crumbs = $this->crumbs($request, $courseId, $chapterId);
        $search = trim((string) $request->input('search'));

        $decks = $crumbs['chapter']->flashcards()
            ->withCount('assessments')
            ->with('flashcardable')
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('student.chapters.flashcards.index', $crumbs + [
            'decks' => FlashcardDeckResource::forView($decks),
            'search' => $search,
        ]);
    }

    /**
     * Study mode for one deck: its cards, stepped through in the page.
     *
     * The URL names the deck, not a card — where the reader is in the deck is
     * a client-side position, so refreshing always starts at the front.
     */
    public function showFlashcard(Request $request, $courseId, $chapterId, $flashcardId)
    {
        $crumbs = $this->crumbs($request, $courseId, $chapterId);

        $deck = Flashcard::where('uuid', $flashcardId)->firstOrFail();
        abort_if($deck->chapter_id !== $crumbs['chapter']->id, 404);

        $cards = $deck->assessments()
            ->with(['question.answer.option', 'question.options'])
            ->get()
            ->filter(fn ($assessment) => $assessment->question)
            ->map(fn ($assessment) => $this->cardFor($assessment->question))
            ->values();

        return view('student.chapters.flashcards.show', $crumbs + [
            'deck' => $deck,
            'cards' => $cards,
            'flashcardId' => $flashcardId,
        ]);
    }

    /**
     * One question turned into a flashcard face.
     *
     * The answer is whichever the question actually carries: the option marked
     * correct for a multiple choice, the expected answer for a written one.
     * `description` is the reasoning, so it becomes the explanation rather than
     * standing in as the answer.
     */
    private function cardFor(QuestionBank $question): array
    {
        $answer = $question->answer->first();
        $clean = fn ($value) => trim(strip_tags((string) $value));

        $text = $clean($answer?->option?->title)
            ?: $clean($answer?->expected_answer)
            ?: $clean($answer?->description);

        $explanation = $clean($answer?->description);

        return [
            'question' => $clean($question->question),
            'answer' => $text ?: 'No answer recorded',
            // The reasoning, but only when it says something the answer did
            // not — it is often the same sentence.
            'explanation' => $explanation === $text ? '' : $explanation,
        ];
    }

    /**
     * The chapter's guides, searchable and filterable by kind.
     */
    public function guides(Request $request, $courseId, $chapterId)
    {
        $crumbs = $this->crumbs($request, $courseId, $chapterId);
        $search = trim((string) $request->input('search'));
        $type = $request->input('type');

        $guides = $crumbs['chapter']->guides()
            ->with(['topic:id,title', 'addedBy:id,name'])
            ->when($search, fn ($query) => $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%")))
            ->when(array_key_exists((string) $type, Guide::TYPE_LABELS), fn ($query) => $query->where('type', $type))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        // Read before the collection is resolved to arrays: forView()
        // maps the paginator in place.
        $state = $this->progress->completionFor($request->user(), $guides->getCollection());

        return view('student.chapters.guides.index', $crumbs + [
            'guides' => GuideResource::forView($guides),
            'search' => $search,
            'type' => $type,
            'state' => $state,
        ]);
    }

    /**
     * One guide, with a contents rail built from its own headings.
     */
    public function showGuide(Request $request, $courseId, $chapterId, $guideId)
    {
        $crumbs = $this->crumbs($request, $courseId, $chapterId);
        $chapter = $crumbs['chapter'];

        $guide = Guide::where('uuid', $guideId)
            ->with(['topic:id,title', 'addedBy:id,name', 'flashcards'])
            ->firstOrFail();

        abort_if($guide->chapter_id !== $chapter->id, 404);

        $siblings = $chapter->guides()->orderByDesc('id')->get(['id', 'uuid', 'title']);
        $position = $siblings->search(fn ($item) => $item->id === $guide->id);

        [$content, $headings] = $this->withAnchors((string) $guide->content);

        return view('student.chapters.guides.show', $crumbs + [
            'guide' => $guide,
            'content' => $content,
            'headings' => $headings,
            'previous' => $position > 0 ? $siblings[$position - 1] : null,
            'next' => $siblings[$position + 1] ?? null,
        ]);
    }

    /**
     * The chapter's diagrams, searchable and filterable by topic.
     */
    public function diagrams(Request $request, $courseId, $chapterId)
    {
        $crumbs = $this->crumbs($request, $courseId, $chapterId);
        $search = trim((string) $request->input('search'));
        $topic = $request->input('topic');

        $diagrams = $crumbs['chapter']->diagrams()
            ->with(['topic:id,title', 'addedBy:id,name'])
            ->when($search, fn ($query) => $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%")))
            ->when($topic, fn ($query) => $query->whereHas(
                'topic',
                fn ($q) => $q->where('uuid', $topic)
            ))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        // Read before the collection is resolved to arrays: forView()
        // maps the paginator in place.
        $state = $this->progress->completionFor($request->user(), $diagrams->getCollection());

        return view('student.chapters.diagrams.index', $crumbs + [
            'diagrams' => DiagramResource::forView($diagrams),
            'search' => $search,
            'topic' => $topic,
            'topics' => $crumbs['chapter']->topics()
                ->whereHas('diagrams')
                ->orderBy('title')
                ->get(['id', 'uuid', 'title']),
            'state' => $state,
        ]);
    }

    public function showDiagram(Request $request, $courseId, $chapterId, $diagramId)
    {
        $crumbs = $this->crumbs($request, $courseId, $chapterId);
        $chapter = $crumbs['chapter'];

        $diagram = Diagram::where('uuid', $diagramId)
            ->with(['topic:id,title', 'addedBy:id,name'])
            ->firstOrFail();

        abort_if($diagram->chapter_id !== $chapter->id, 404);

        // Ordered the same way the listing is, so "next" means what it looks
        // like it means coming from there.
        $siblings = $chapter->diagrams()->orderByDesc('id')->get(['id', 'uuid', 'title']);
        $position = $siblings->search(fn ($item) => $item->id === $diagram->id);

        return view('student.chapters.diagrams.show', $crumbs + [
            'diagram' => $diagram,
            'diagramId' => $diagramId,
            'previous' => $position > 0 ? $siblings[$position - 1] : null,
            'next' => $siblings[$position + 1] ?? null,
        ]);
    }

    /**
     * The chapter's summaries, searchable and filterable by topic.
     */
    public function summaries(Request $request, $courseId, $chapterId)
    {
        $crumbs = $this->crumbs($request, $courseId, $chapterId);
        $search = trim((string) $request->input('search'));
        $topic = $request->input('topic');

        $summaries = $crumbs['chapter']->summaries()
            ->with('topic:id,title')
            ->when($search, fn ($query) => $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%")))
            ->when($topic, fn ($query) => $query->whereHas(
                'topic',
                fn ($q) => $q->where('uuid', $topic)
            ))
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        // Read before the collection is resolved to arrays: forView()
        // maps the paginator in place.
        $state = $this->progress->completionFor($request->user(), $summaries->getCollection());

        return view('student.chapters.summaries.index', $crumbs + [
            'summaries' => SummaryResource::forView($summaries),
            'search' => $search,
            'topic' => $topic,
            // Only topics this chapter actually has summaries under.
            'topics' => $crumbs['chapter']->topics()
                ->whereHas('summaries')
                ->orderBy('title')
                ->get(['id', 'uuid', 'title']),
            'state' => $state,
        ]);
    }

    /**
     * One summary, with what to do next once it has been read.
     */
    public function showSummary(Request $request, $courseId, $chapterId, $summaryId)
    {
        $crumbs = $this->crumbs($request, $courseId, $chapterId);
        $chapter = $crumbs['chapter'];

        $summary = Summary::where('uuid', $summaryId)
            ->with(['topic:id,title', 'attachments', 'flashcards'])
            ->firstOrFail();

        abort_if($summary->chapter_id !== $chapter->id, 404);

        // Ordered the same way the listing is, so "next" means what it looks
        // like it means coming from there.
        $siblings = $chapter->summaries()->orderByDesc('id')->get(['id', 'uuid', 'title']);
        $position = $siblings->search(fn ($item) => $item->id === $summary->id);

        return view('student.chapters.summaries.show', $crumbs + [
            'summary' => $summary,
            'previous' => $position > 0 ? $siblings[$position - 1] : null,
            'next' => $siblings[$position + 1] ?? null,
            // Somewhere to go once it is read.
            'quiz' => $chapter->quizzes()->withCount('questions')->latest('quizzes.id')->first(),
        ]);
    }

    /**
     * The chapter's video lessons, searchable and sortable.
     */
    public function videos(Request $request, $courseId, $chapterId)
    {
        $crumbs = $this->crumbs($request, $courseId, $chapterId);
        $search = trim((string) $request->input('search'));

        $sorts = [
            'newest' => ['id', 'desc'],
            'oldest' => ['id', 'asc'],
            'title' => ['title', 'asc'],
        ];
        $sort = array_key_exists((string) $request->input('sort'), $sorts)
            ? $request->input('sort')
            : 'newest';

        [$column, $direction] = $sorts[$sort];

        $videos = $crumbs['chapter']->videoLessons()
            ->with(['addedBy:id,name', 'topic:id,title', 'video'])
            ->when($search, fn ($query) => $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->orderBy($column, $direction)
            ->paginate(12)
            ->withQueryString();

        // Read before the collection is resolved to arrays: forView()
        // maps the paginator in place.
        $state = $this->progress->completionFor($request->user(), $videos->getCollection());

        return view('student.chapters.videos.index', $crumbs + [
            'videos' => VideoLessonResource::forView($videos),
            'search' => $search,
            'sort' => $sort,
            'sorts' => ['newest' => 'Newest first', 'oldest' => 'Oldest first', 'title' => 'Title A–Z'],
            // How far through each lesson this student is.
            'state' => $state,
        ]);
    }

    /**
     * One video lesson, with the chapter's playlist beside it.
     */
    public function showVideo(Request $request, $courseId, $chapterId, $videoId)
    {
        $crumbs = $this->crumbs($request, $courseId, $chapterId);
        $chapter = $crumbs['chapter'];

        $video = VideoLesson::where('uuid', $videoId)
            ->with(['addedBy:id,name', 'topic:id,title', 'video'])
            ->firstOrFail();

        abort_if($video->chapter_id !== $chapter->id, 404);

        $playlist = $chapter->videoLessons()->with('video')->orderBy('id')->get();
        $position = $playlist->search(fn ($item) => $item->id === $video->id);
        $state = $this->progress->completionFor($request->user(), $playlist);

        return view('student.chapters.videos.show', $crumbs + [
            'video' => $video,
            'playlist' => $playlist,
            'state' => $state,
            'watched' => $state[$video->id]['progress'] ?? 0,
            // Completion is its own flag: a lesson finishes at 90% watched, so
            // the percentage alone would still read as unfinished.
            'done' => $state[$video->id]['completed'] ?? false,
            'completedCount' => collect($state)->where('completed', true)->count(),
            'next' => $playlist[$position + 1] ?? null,
            'quiz' => $chapter->quizzes()->withCount('questions')->latest('quizzes.id')->first(),
        ]);
    }

    /**
     * What every resource page under a chapter needs: the pair from the URL,
     * so breadcrumbs can name them, plus the raw uuids the links are built
     * from. Going through locate() means these pages are behind the same
     * paywall as the chapter itself.
     */
    private function crumbs(Request $request, string $courseId, string $chapterId): array
    {
        [$course, $chapter] = $this->locate($request, $courseId, $chapterId);

        return compact('course', 'chapter', 'courseId', 'chapterId');
    }
}

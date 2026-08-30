<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Diagram;
use App\Models\Flashcard;
use App\Models\Guide;
use App\Models\Note;
use App\Models\QuestionBank;
use App\Models\Quiz;
use App\Models\Summary;
use App\Models\Topic;
use App\Models\User;
use App\Models\VideoLesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Backs the global search box in both headers. Each hit carries the URL it
 * belongs to, so choosing one navigates straight there.
 */
class SearchController extends Controller
{
    /**
     * How many hits of each kind to return, so no one kind floods the list.
     */
    private const PER_TYPE = 5;

    /**
     * Search the admin panel.
     */
    public function admin(Request $request): JsonResponse
    {
        return $this->respond($request, $this->adminGroups($request));
    }

    /**
     * Search the student portal. Only public chapters and what hangs off them
     * are reachable, so only those are searched.
     */
    public function student(Request $request): JsonResponse
    {
        return $this->respond($request, $this->studentGroups($request));
    }

    /**
     * Everything an admin can search, keyed by the pill it belongs to.
     */
    private function adminGroups(Request $request): array
    {
        $term = $this->term($request);

        return [
            'courses' => $this->hits(
                Course::query()->with('category:id,title'),
                'title', $term,
                fn (Course $c) => [
                    'title' => $c->title,
                    'meta' => $c->category?->title ?: 'Course',
                    'url' => route('courses.chapters', $c),
                ]
            ),
            'chapters' => $this->hits(
                Chapter::query()->with('course:id,uuid,title'),
                'title', $term,
                fn (Chapter $c) => [
                    'title' => $c->title,
                    'meta' => 'Chapter ' . $c->chapter_number . ($c->course ? ' · ' . $c->course->title : ''),
                    'url' => $c->course ? route('courses.chapters.dashboard', [$c->course, $c]) : null,
                ]
            ),
            'topics' => $this->hits(
                Topic::query()->with('chapter.course:id,uuid,title'),
                'title', $term,
                fn (Topic $t) => [
                    'title' => $t->title,
                    'meta' => $t->chapter?->title ?: 'Topic',
                    'url' => $t->chapter?->course
                        ? route('topics', [$t->chapter->course, $t->chapter, 'title' => $t->title, 'open' => $t->uuid])
                        : null,
                ]
            ),
            'notes' => $this->chapterScoped(Note::class, $term, 'notes'),
            'guides' => $this->chapterScoped(Guide::class, $term, 'guides'),
            // A deck has a page of its own — the builder — so go straight there.
            'flashcards' => $this->hits(
                Flashcard::query()->with('chapter.course:id,uuid,title'),
                'title', $term,
                fn (Flashcard $f) => [
                    'title' => $f->title,
                    'meta' => $f->chapter?->title ?: 'Flashcards',
                    'url' => $f->chapter?->course
                        ? route('flashcards.builder', [$f->chapter->course, $f->chapter, $f])
                        : null,
                ]
            ),
            'summaries' => $this->hits(
                Summary::query()->with('chapter:id,title'),
                'title', $term,
                fn (Summary $s) => [
                    'title' => $s->title,
                    'meta' => $s->chapter?->title ?: 'Summary',
                    'url' => route('summaries', ['title' => $s->title, 'open' => $s->uuid]),
                ]
            ),
            'diagrams' => $this->hits(
                Diagram::query()->with('chapter:id,title'),
                'title', $term,
                fn (Diagram $d) => [
                    'title' => $d->title,
                    'meta' => $d->chapter?->title ?: 'Diagram',
                    'url' => route('diagrams', ['title' => $d->title, 'open' => $d->uuid]),
                ]
            ),
            'videos' => $this->hits(
                VideoLesson::query()->with('chapter:id,title'),
                'title', $term,
                fn (VideoLesson $v) => [
                    'title' => $v->title,
                    'meta' => $v->chapter?->title ?: 'Video lesson',
                    'url' => route('videos', ['title' => $v->title, 'open' => $v->uuid]),
                ]
            ),
            'quizzes' => $this->hits(
                Quiz::query(),
                'title', $term,
                fn (Quiz $q) => [
                    'title' => $q->title,
                    'meta' => ucfirst($q->status) . ' · ' . (QuizController::TYPES[$q->type] ?? $q->type),
                    'url' => route('quizzes.edit', $q),
                ]
            ),
            'questions' => $this->hits(
                QuestionBank::query()->with('chapter:id,title'),
                'question', $term,
                fn (QuestionBank $q) => [
                    'title' => $q->plain_question,
                    'meta' => $q->chapter?->title ?: 'Question bank',
                    'url' => route('questions', ['question' => $q->question, 'open' => $q->uuid]),
                ]
            ),
            'categories' => $this->hits(
                Category::query(),
                'title', $term,
                fn (Category $c) => [
                    'title' => $c->title,
                    'meta' => 'Category',
                    'url' => route('categories'),
                ]
            ),
            'students' => $this->hits(
                User::query()->whereHas('roles', fn ($r) => $r->where('name', 'student')),
                'name', $term,
                fn (User $u) => [
                    'title' => $u->name,
                    'meta' => $u->email,
                    'url' => route('students'),
                ]
            ),
        ];
    }

    /**
     * Everything a student can search — the public catalogue only.
     */
    private function studentGroups(Request $request): array
    {
        $term = $this->term($request);

        // A chapter is only reachable when the course marked it public.
        $public = fn ($query) => $query->where('visibility', 'public');

        return [
            'courses' => $this->hits(
                Course::query()->with('category:id,title'),
                'title', $term,
                fn (Course $c) => [
                    'title' => $c->title,
                    'meta' => $c->category?->title ?: 'Course',
                    'url' => route('public.course.chapters', $c),
                ]
            ),
            'chapters' => $this->hits(
                Chapter::query()->where('visibility', 'public')->with('course:id,uuid,title'),
                'title', $term,
                fn (Chapter $c) => [
                    'title' => $c->title,
                    'meta' => 'Chapter ' . $c->chapter_number . ($c->course ? ' · ' . $c->course->title : ''),
                    'url' => $c->course ? route('public.course.chapter.show', [$c->course, $c]) : null,
                ]
            ),
            'notes' => $this->hits(
                Note::query()->whereHas('chapter', $public)->with('chapter.course:id,uuid,title'),
                'title', $term,
                fn (Note $n) => [
                    'title' => $n->title,
                    'meta' => $n->chapter?->title ?: 'Study note',
                    'url' => $n->chapter?->course
                        ? route('public.course.chapter.notes.show', [$n->chapter->course, $n->chapter, $n])
                        : null,
                ]
            ),
            'flashcards' => $this->hits(
                Flashcard::query()->whereHas('chapter', $public)->with('chapter.course:id,uuid,title'),
                'title', $term,
                fn (Flashcard $f) => [
                    'title' => $f->title,
                    'meta' => $f->chapter?->title ?: 'Flashcards',
                    'url' => $f->chapter?->course
                        ? route('public.course.chapter.flashcards.show', [$f->chapter->course, $f->chapter, $f])
                        : null,
                ]
            ),
            'quizzes' => $this->hits(
                Quiz::query()->whereStatus('published')->whereIn('type', ['mcqs', 'theory'])
                    ->with('chapters'),
                'title', $term,
                function (Quiz $q) {
                    $chapter = $q->chapters->firstWhere('visibility', 'public');

                    return [
                        'title' => $q->title,
                        'meta' => $q->type === 'mcqs' ? 'MCQ quiz' : 'Theory practice',
                        'url' => $chapter && $chapter->course
                            ? route(
                                $q->type === 'mcqs'
                                    ? 'public.course.chapter.mcqs.show'
                                    : 'public.course.chapter.theory.show',
                                [$chapter->course, $chapter, $q]
                            )
                            : null,
                    ];
                }
            ),
        ];
    }

    /**
     * A resource that lives under course › chapter and lists there.
     */
    private function chapterScoped(string $class, string $term, string $routeName): array
    {
        return $this->hits(
            $class::query()->with('chapter.course:id,uuid,title'),
            'title', $term,
            fn ($record) => [
                'title' => $record->title,
                'meta' => $record->chapter?->title ?: ucfirst($routeName),
                'url' => $record->chapter?->course
                    ? route($routeName, [$record->chapter->course, $record->chapter, 'title' => $record->title, 'open' => $record->uuid])
                    : null,
            ]
        );
    }

    /**
     * Runs one group's query and shapes its rows. Hits without a URL are
     * dropped — there would be nowhere to send the reader.
     */
    private function hits($query, string $column, string $term, callable $shape): array
    {
        if ($term === '') {
            return [];
        }

        return $query->where($column, 'like', "%{$term}%")
            ->latest('id')
            ->limit(self::PER_TYPE)
            ->get()
            ->map($shape)
            ->filter(fn ($hit) => ! empty($hit['url']))
            ->map(fn ($hit) => [
                'title' => \Illuminate\Support\Str::limit(
                    trim(preg_replace('/\s+/u', ' ', strip_tags((string) $hit['title']))),
                    90
                ),
                'meta' => $hit['meta'],
                'url' => $hit['url'],
            ])
            ->values()
            ->all();
    }

    /**
     * The term to match, or an empty string when it is too short to be useful.
     */
    private function term(Request $request): string
    {
        $term = trim((string) $request->input('q', ''));

        return mb_strlen($term) >= 2 ? $term : '';
    }

    /**
     * Narrows to the chosen modules, drops empty groups, and counts what is
     * left. Any number of modules can be picked; none means all of them.
     */
    private function respond(Request $request, array $groups): JsonResponse
    {
        $wanted = array_filter((array) $request->input('types', []));

        if ($wanted && ! in_array('all', $wanted, true)) {
            $groups = array_intersect_key($groups, array_flip($wanted));
        }

        $groups = array_filter($groups);

        return response()->json([
            'term' => $this->term($request),
            'types' => $wanted,
            'total' => array_sum(array_map('count', $groups)),
            'groups' => $groups,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}

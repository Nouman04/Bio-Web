<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\QuestionBank;
use App\Models\Topic;
use App\Models\User;
use App\Models\Worksheet;
use App\Models\WorksheetChapter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Building a worksheet out of the question bank, and reading one back for the
 * question paper and the mark scheme.
 *
 * The picked questions are stored in `assessments` — the same table the
 * flashcard decks use — with `assessmentable` pointing at the worksheet. Which
 * chapters were drawn from is kept in `worksheets_chapters`.
 */
class WorksheetService
{
    /**
     * Questions reach a topic through the polymorphic `questionable_type`
     * table, and their past-paper details hang off that row.
     */
    private const TOPIC_TYPE = Topic::class;

    /**
     * The past-paper questions a selection asks for.
     *
     * Choices inside one box are alternatives; the boxes themselves stack up.
     * Topic A and topic B with 2024 means "from either topic, and from 2024" —
     * so a topic with nothing in 2024 simply contributes nothing, while the one
     * that does contributes its 2024 questions. An empty box asks for no
     * restriction at all.
     *
     * Only questions that actually came off a past paper are eligible; a
     * question with no past-paper reference is not past-paper material.
     *
     * @param  array{course?:string|null, chapters?:array, topics?:array, papers?:array, years?:array}  $filters
     */
    public function questions(array $filters): Builder
    {
        $questions = QuestionBank::query()
            ->with(['category:id,type', 'answer', 'options'])
            ->select('question_bank.*');

        // Scope: the course, and nothing outside it.
        if ($courseChapters = $this->courseChapterIds($filters)) {
            $questions->whereIn('question_bank.chapter_id', $courseChapters);
        }

        if ($chapters = array_filter((array) ($filters['chapters'] ?? []))) {
            $questions->whereIn(
                'question_bank.chapter_id',
                Chapter::whereIn('uuid', $chapters)->select('id')
            );
        }

        $topics = array_filter((array) ($filters['topics'] ?? []));
        $papers = array_filter((array) ($filters['papers'] ?? []));
        $years = array_filter((array) ($filters['years'] ?? []));

        // Topic, paper and year are all read off the same past-paper row: a
        // question belongs on this worksheet because that appearance matches,
        // not because it once appeared somewhere else for another reason.
        $questions->whereExists(function ($query) use ($topics, $papers, $years) {
            $this->pastPaperRows($query);

            if ($topics) {
                $query->whereIn('qt.questionable_id', Topic::whereIn('uuid', $topics)->select('id'));
            }

            if ($papers) {
                $query->whereIn('ppr.paper_no', $papers);
            }

            if ($years) {
                $query->whereIn(DB::raw('YEAR(ppr.date)'), $years);
            }
        });

        return $questions->orderBy('question_bank.id');
    }

    /**
     * The past-paper rows belonging to the question the outer query is on.
     *
     * Eligibility and every past-paper condition hang off this same join, so it
     * is written once.
     */
    private function pastPaperRows($query)
    {
        return $query->select(DB::raw(1))
            ->from('questionable_type as qt')
            ->join('past_paper_reference as ppr', 'ppr.questionable_type_id', '=', 'qt.id')
            ->whereColumn('qt.question_id', 'question_bank.id')
            ->where('qt.questionable_type', self::TOPIC_TYPE);
    }

    /**
     * How many questions the current selection would produce, and what they are
     * made of — the figures the builder shows before anything is generated.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function summarise(array $filters): array
    {
        $questions = $this->questions($filters)->get();

        $byType = $questions->groupBy(fn (QuestionBank $q) => $q->category?->type ?? 'other')
            ->map->count();

        $references = $this->referencesFor($questions->pluck('id'));

        return [
            'total' => $questions->count(),
            'mcqs' => (int) ($byType['mcqs'] ?? 0),
            'theory' => (int) ($byType['theory'] ?? 0),
            'marks' => (float) $references->sum('marks'),
            'papers' => $references->pluck('paper_no')->filter()->unique()->sort()->values()->all(),
            'years' => $references->pluck('date')->filter()
                ->map(fn ($date) => (int) substr((string) $date, 0, 4))
                ->unique()->sort()->values()->all(),
            'topics' => $this->topicTitlesFor($questions->pluck('id')),
            'by_difficulty' => $questions->groupBy(fn (QuestionBank $q) => $q->difficulty_level ?: 'Unspecified')
                ->map->count()->all(),
        ];
    }

    /**
     * The past-paper rows behind a set of questions.
     *
     * @param  Collection<int, int>  $questionIds
     */
    public function referencesFor(Collection $questionIds): Collection
    {
        if ($questionIds->isEmpty()) {
            return collect();
        }

        return DB::table('past_paper_reference as ppr')
            ->join('questionable_type as qt', 'qt.id', '=', 'ppr.questionable_type_id')
            ->whereIn('qt.question_id', $questionIds)
            ->where('qt.questionable_type', self::TOPIC_TYPE)
            ->select('qt.question_id', 'ppr.date', 'ppr.paper_no', 'ppr.question_no', 'ppr.marks', 'ppr.source')
            ->get();
    }

    /**
     * The reference for each question, keyed by question id. A question can
     * appear on more than one paper; the first is the one printed.
     *
     * @param  Collection<int, int>  $questionIds
     * @return array<int, object>
     */
    public function referenceMap(Collection $questionIds): array
    {
        return $this->referencesFor($questionIds)
            ->groupBy('question_id')
            ->map->first()
            ->all();
    }

    /**
     * The topics a set of questions belongs to.
     *
     * @param  Collection<int, int>  $questionIds
     * @return array<int, string>
     */
    public function topicTitlesFor(Collection $questionIds): array
    {
        if ($questionIds->isEmpty()) {
            return [];
        }

        return DB::table('questionable_type as qt')
            ->join('topics', 'topics.id', '=', 'qt.questionable_id')
            ->whereIn('qt.question_id', $questionIds)
            ->where('qt.questionable_type', self::TOPIC_TYPE)
            ->distinct()
            ->orderBy('topics.title')
            ->pluck('topics.title')
            ->all();
    }

    /* ── Building ───────────────────────────────────────────────────────── */

    /**
     * Records a worksheet: the row itself, the chapters it drew from, and every
     * question it picked up, written to `assessments`.
     *
     * @param  array<string, mixed>  $filters
     */
    public function create(User $author, array $filters, string $title): Worksheet
    {
        $course = Course::where('uuid', $filters['course'] ?? null)->firstOrFail();
        $questions = $this->questions($filters)->get();

        return DB::transaction(function () use ($author, $course, $filters, $title, $questions) {
            $worksheet = Worksheet::create([
                'course_id' => $course->id,
                'created_by' => $author->id,
                'title' => $title,
                // Kept so the cover page can list everything that was asked
                // for, including a topic that turned out to have nothing in
                // the chosen year.
                'filters' => [
                    'chapters' => array_values(array_filter((array) ($filters['chapters'] ?? []))),
                    'topics' => array_values(array_filter((array) ($filters['topics'] ?? []))),
                    'papers' => array_values(array_filter((array) ($filters['papers'] ?? []))),
                    'years' => array_values(array_filter((array) ($filters['years'] ?? []))),
                ],
            ]);

            // The chapters the picked questions actually came from, which is
            // what the cover page should list — not every chapter of the course.
            $chapterIds = $questions->pluck('chapter_id')->filter()->unique()->values();

            foreach ($chapterIds as $chapterId) {
                WorksheetChapter::create([
                    'worksheet_id' => $worksheet->id,
                    'chapter_id' => $chapterId,
                ]);
            }

            // The questions live in `assessments`, keyed to this worksheet — the
            // same table and shape the flashcard decks already use.
            foreach ($questions->values() as $index => $question) {
                Assessment::create([
                    'assessmentable_type' => Worksheet::class,
                    'assessmentable_id' => $worksheet->id,
                    'question_id' => $question->id,
                    'order' => $index + 1,
                ]);
            }

            return $worksheet;
        });
    }

    /**
     * The questions on a saved worksheet, in the order they were picked.
     *
     * @return Collection<int, QuestionBank>
     */
    public function questionsOf(Worksheet $worksheet): Collection
    {
        return $worksheet->assessments()
            ->with(['question.category:id,type', 'question.answer', 'question.options'])
            ->get()
            ->map(fn (Assessment $row) => $row->question)
            ->filter()
            ->values();
    }

    /**
     * Everything the PDFs print: the questions, their references, and the
     * summary that opens the paper.
     *
     * @return array<string, mixed>
     */
    public function paperFor(Worksheet $worksheet): array
    {
        $questions = $this->questionsOf($worksheet);
        $ids = $questions->pluck('id');
        $references = $this->referenceMap($ids);

        $marks = collect($references)->sum('marks');
        $asked = $worksheet->filters ?? [];

        return [
            'worksheet' => $worksheet->loadMissing('course', 'creator'),
            'questions' => $questions,
            'references' => $references,
            'summary' => [
                'total' => $questions->count(),
                'marks' => (float) $marks,
                'mcqs' => $questions->filter(fn ($q) => $q->category?->type === 'mcqs')->count(),
                'theory' => $questions->filter(fn ($q) => $q->category?->type === 'theory')->count(),

                // Every topic that was asked for, whether or not it had
                // anything to give under the other choices. Worksheets built
                // before the selection was recorded fall back to the topics the
                // questions themselves came from.
                'topics' => ! empty($asked['topics'])
                    ? Topic::whereIn('uuid', $asked['topics'])->orderBy('title')->pluck('title')->all()
                    : $this->topicTitlesFor($ids),

                // Which of those actually contributed, so a reader can see at a
                // glance that one of them drew a blank.
                'topics_used' => $this->topicTitlesFor($ids),
                'chapters' => $worksheet->worksheetChapters()
                    ->with('chapter:id,title')
                    ->get()
                    ->pluck('chapter.title')
                    ->filter()
                    ->values()
                    ->all(),
                'papers' => collect($references)->pluck('paper_no')->filter()->unique()->sort()->values()->all(),
                'years' => collect($references)->pluck('date')->filter()
                    ->map(fn ($date) => (int) substr((string) $date, 0, 4))
                    ->unique()->sort()->values()->all(),
            ],
        ];
    }

    /* ── Options for the builder ────────────────────────────────────────── */

    /**
     * The courses a student may build a worksheet from: the ones they hold a
     * live subscription to, and nothing else.
     *
     * @return Collection<int, Course>
     */
    public function subscribedCourses(User $student): Collection
    {
        $uuids = $student->subscriptions
            ->filter(fn ($subscription) => $subscription->valid())
            ->map(fn ($subscription) => \Illuminate\Support\Str::after($subscription->type, 'course_'))
            ->filter()
            ->unique();

        if ($uuids->isEmpty()) {
            return collect();
        }

        return Course::whereIn('uuid', $uuids)->orderBy('title')->get(['id', 'uuid', 'title']);
    }

    /**
     * The chapters of one course.
     *
     * @return Collection<int, Chapter>
     */
    public function chaptersOf(?string $courseUuid): Collection
    {
        if (! $courseUuid) {
            return collect();
        }

        return Chapter::whereIn('course_id', Course::where('uuid', $courseUuid)->select('id'))
            ->orderBy('chapter_number')
            ->get(['id', 'uuid', 'title', 'chapter_number']);
    }

    /**
     * The topics available under a set of chapters.
     *
     * @return Collection<int, Topic>
     */
    public function topicsOf(array $chapterUuids, ?string $courseUuid = null): Collection
    {
        // Every topic on the course. Topics collect alongside the chapters
        // rather than sitting under them, so a chapter choice must not hide
        // the topics a reader might also want to pull in.
        $chapters = $this->chaptersOf($courseUuid)->pluck('id');

        if ($chapters->isEmpty()) {
            return collect();
        }

        return Topic::whereIn('chapter_id', $chapters)
            ->orderBy('title')
            ->get(['id', 'uuid', 'title', 'chapter_id']);
    }

    /**
     * Every paper number the bank actually holds.
     *
     * @return array<int, string>
     */
    public function paperNumbers(): array
    {
        return DB::table('past_paper_reference')
            ->whereNotNull('paper_no')
            ->distinct()
            ->orderBy('paper_no')
            ->pluck('paper_no')
            ->all();
    }

    /**
     * Every year the bank actually holds, newest first.
     *
     * @return array<int, int>
     */
    public function years(): array
    {
        return DB::table('past_paper_reference')
            ->whereNotNull('date')
            ->selectRaw('DISTINCT YEAR(date) as year')
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->all();
    }

    /**
     * Every chapter of the chosen course — the scope the worksheet is drawn
     * from. Empty means no course was chosen, which scopes to nothing.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, int>
     */
    private function courseChapterIds(array $filters): array
    {
        if (! $course = ($filters['course'] ?? null)) {
            return [];
        }

        return Chapter::whereIn('course_id', Course::where('uuid', $course)->select('id'))
            ->pluck('id')
            ->all();
    }
}

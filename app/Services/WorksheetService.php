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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
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
     * Where a citation can hang: a topic, or - for a past-paper part not yet
     * tagged with a chapter - the course.
     */
    private const CITED_TYPES = [Topic::class, Course::class];

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
     * Questions are the top question_bank rows; their parts sit under them
     * through parent_id. With a chapter or topic chosen, a question qualifies
     * when it, or a part under it, is tagged to the choice (tagged()); which
     * parts then print is decided by partsFor(). With none chosen, every
     * question qualifies whole.
     *
     * @param  array{course?:string|null, chapters?:array, topics?:array, papers?:array, years?:array}  $filters
     */
    public function questions(array $filters): Builder
    {
        $questions = QuestionBank::query()
            ->with(array_merge(['category:id,type', 'options'], QuestionBank::TREE))
            ->select('question_bank.*')
            ->whereNull('question_bank.parent_id');

        // Scope: the course, and nothing outside it. course_id is set with or
        // without a chapter, so untagged imported questions are in scope too.
        if ($course = ($filters['course'] ?? null)) {
            $questions->whereIn('question_bank.course_id', Course::where('uuid', $course)->select('id'));
        }

        if (($tagged = $this->tagged($filters)) !== null) {
            $questions->whereIn('question_bank.id', $this->topsOf($tagged));
        }

        $papers = array_filter((array) ($filters['papers'] ?? []));
        $years = array_filter((array) ($filters['years'] ?? []));

        // Paper and year come off the question's past-paper row.
        $questions->whereExists(function ($query) use ($papers, $years) {
            $this->pastPaperRows($query);

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
     * Ids of the questions and parts tagged to the chosen chapters and topics:
     * chapter on the row, topic through its link. Null when neither was
     * chosen, which asks for no restriction.
     *
     * @return array<int, int>|null
     */
    private function tagged(array $filters): ?array
    {
        $chapters = array_filter((array) ($filters['chapters'] ?? []));
        $topics = array_filter((array) ($filters['topics'] ?? []));

        if (! $chapters && ! $topics) {
            return null;
        }

        return QuestionBank::query()
            ->when($chapters, fn ($q) => $q->whereIn('chapter_id', Chapter::whereIn('uuid', $chapters)->select('id')))
            ->when($topics, fn ($q) => $q->whereHas('questionables', fn ($link) => $link
                ->where('questionable_type', self::TOPIC_TYPE)
                ->whereIn('questionable_id', Topic::whereIn('uuid', $topics)->select('id'))))
            ->pluck('id')
            ->all();
    }

    /**
     * The top questions the given rows sit under (a top row is its own).
     *
     * @param  array<int, int>  $ids
     * @return array<int, int>
     */
    private function topsOf(array $ids): array
    {
        $tops = [];
        $rows = QuestionBank::whereIn('id', $ids)->get(['id', 'parent_id']);

        while ($rows->isNotEmpty()) {
            foreach ($rows->whereNull('parent_id') as $row) {
                $tops[$row->id] = $row->id;
            }
            $rows = QuestionBank::whereIn('id', $rows->pluck('parent_id')->filter()->unique())->get(['id', 'parent_id']);
        }

        return array_values($tops);
    }

    /**
     * Which parts of a question print, in paper order: the parts at the end of
     * each branch (they carry the marks) that were tagged to the choice - all
     * of them when nothing was chosen - and every lead-in above one of those.
     *
     * @param  array<int, int>|null  $tagged  from tagged()
     * @return Collection<int, QuestionBank>
     */
    public function partsFor(QuestionBank $question, ?array $tagged): Collection
    {
        $parts = $question->descendantRows()->keyBy('id');
        $parents = $parts->pluck('parent_id')->flip();
        $taggedIds = array_flip($tagged ?? []);

        $picked = $parts->filter(fn ($part) => ! $parents->has($part->id)
            && ($tagged === null || isset($taggedIds[$part->id])));

        $keep = [];
        foreach ($picked as $part) {
            for ($node = $part; $node; $node = $parts->get($node->parent_id)) {
                $keep[$node->id] = true;
            }
        }

        return $parts->filter(fn ($part) => isset($keep[$part->id]))->values();
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
            ->whereIn('qt.questionable_type', self::CITED_TYPES);
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
        [$printed, $references] = $this->printable($questions, $filters);

        $byType = $questions->groupBy(fn (QuestionBank $q) => $q->category?->type ?? 'other')
            ->map->count();

        return [
            'total' => $questions->count(),
            'mcqs' => (int) ($byType['mcqs'] ?? 0),
            'theory' => (int) ($byType['theory'] ?? 0),
            'marks' => (float) collect($references)->sum('marks'),
            'papers' => collect($references)->pluck('paper_no')->filter()->unique()->sort()->values()->all(),
            'years' => collect($references)->pluck('date')->filter()
                ->map(fn ($date) => (int) substr((string) $date, 0, 4))
                ->unique()->sort()->values()->all(),
            'topics' => $this->topicTitlesFor($printed),
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
            ->whereIn('qt.questionable_type', self::CITED_TYPES)
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
     * The topics the printed questions came from: a whole question's own
     * topic, or its printed parts' topics.
     *
     * @param  Collection<int, QuestionBank>  $printed
     * @return array<int, string>
     */
    public function topicTitlesFor(Collection $printed): array
    {
        return $this->topicTitlesForIds(
            $printed->flatMap(fn (QuestionBank $q) => collect([$q->id])->merge($q->getRelation('printed')->pluck('id')))
        );
    }

    /**
     * The topics linked to these questions and parts.
     *
     * @param  Collection<int, int>  $ids
     * @return array<int, string>
     */
    private function topicTitlesForIds(Collection $ids): array
    {
        if ($ids->isEmpty()) {
            return [];
        }

        return DB::table('questionable_type as qt')
            ->join('topics', 'topics.id', '=', 'qt.questionable_id')
            ->whereIn('qt.question_id', $ids)
            ->where('qt.questionable_type', self::TOPIC_TYPE)
            ->distinct()
            ->orderBy('topics.title')
            ->pluck('topics.title')
            ->all();
    }

    /* ── Building ───────────────────────────────────────────────────────── */

    /**
     * Records a worksheet: the row itself, the chapters it drew from, and every
     * question it picked up, written to `assessments`. Which parts of each
     * question print is worked out again from the saved filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function create(User $author, array $filters, string $title): Worksheet
    {
        $course = Course::where('uuid', $filters['course'] ?? null)->firstOrFail();
        $questions = $this->questions($filters)->get();
        [$printed] = $this->printable($questions, $filters);

        return DB::transaction(function () use ($author, $course, $filters, $title, $questions, $printed) {
            $worksheet = Worksheet::create([
                'course_id' => $course->id,
                'created_by' => $author->id,
                'title' => $title,
                // Kept so the cover page can list everything that was asked
                // for, including a topic that turned out to have nothing in
                // the chosen year - and so the same parts print every time.
                'filters' => [
                    'chapters' => array_values(array_filter((array) ($filters['chapters'] ?? []))),
                    'topics' => array_values(array_filter((array) ($filters['topics'] ?? []))),
                    'papers' => array_values(array_filter((array) ($filters['papers'] ?? []))),
                    'years' => array_values(array_filter((array) ($filters['years'] ?? []))),
                ],
            ]);

            // The chapters the printed questions and parts actually came from,
            // which is what the cover page should list.
            $chapterIds = $printed
                ->flatMap(fn (QuestionBank $q) => collect([$q->chapter_id])->merge($q->getRelation('printed')->pluck('chapter_id')))
                ->filter()->unique()->values();

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
            ->with(array_merge(['question.category:id,type', 'question.options'], array_map(fn ($relation) => 'question.' . $relation, QuestionBank::TREE)))
            ->get()
            ->map(fn (Assessment $row) => $row->question)
            ->filter()
            ->values();
    }

    /**
     * Everything the PDFs print: the questions, their references, and the
     * summary that opens the paper.
     *
     * With a page, `questions` is that page of a paginator instead of every
     * question: the summary still covers the whole worksheet, but only the
     * page's questions load their text and answers.
     *
     * @return array<string, mixed>
     */
    public function paperFor(Worksheet $worksheet, ?int $page = null, int $perPage = 12): array
    {
        $asked = $worksheet->filters ?? [];

        if ($page === null) {
            [$questions, $references] = $this->printable($this->questionsOf($worksheet), $asked);
            $printedIds = $questions->flatMap(fn (QuestionBank $q) => collect([$q->id])->merge($q->getRelation('printed')->pluck('id')));
            $tally = [
                'total' => $questions->count(),
                'marks' => (float) collect($references)->sum('marks'),
                'mcqs' => $questions->filter(fn ($q) => $q->category?->type === 'mcqs')->count(),
                'theory' => $questions->filter(fn ($q) => $q->category?->type === 'theory')->count(),
                'refs' => collect($references),
            ];
        } else {
            // A page of a long worksheet: the figures for all of it come from
            // plain rows, and only this page's questions load as models.
            [$tally, $printedIds, $pageIds] = $this->tally($worksheet, $asked, $page, $perPage);
            $full = QuestionBank::with(array_merge(['category:id,type', 'options'], QuestionBank::TREE))
                ->whereIn('id', $pageIds)
                ->get()
                ->sortBy(fn (QuestionBank $q) => $pageIds->search($q->id))
                ->values();
            [$onPage, $references] = $this->printable($full, $asked);

            $questions = new LengthAwarePaginator($onPage, $tally['total'], $perPage, $page, [
                'path' => Paginator::resolveCurrentPath(),
            ]);
        }

        $refs = $tally['refs'];
        $topicsUsed = $this->topicTitlesForIds($printedIds);

        return [
            'worksheet' => $worksheet->loadMissing('course', 'creator'),
            'questions' => $questions,
            'references' => $references,
            'summary' => [
                'total' => $tally['total'],
                'marks' => $tally['marks'],
                'mcqs' => $tally['mcqs'],
                'theory' => $tally['theory'],

                // Every topic that was asked for, whether or not it had
                // anything to give under the other choices. Worksheets built
                // before the selection was recorded fall back to the topics the
                // questions themselves came from.
                'topics' => ! empty($asked['topics'])
                    ? Topic::whereIn('uuid', $asked['topics'])->orderBy('title')->pluck('title')->all()
                    : $topicsUsed,

                // Which of those actually contributed, so a reader can see at a
                // glance that one of them drew a blank.
                'topics_used' => $topicsUsed,
                'chapters' => $worksheet->worksheetChapters()
                    ->with('chapter:id,title')
                    ->get()
                    ->pluck('chapter.title')
                    ->filter()
                    ->values()
                    ->all(),
                'papers' => $refs->pluck('paper_no')->filter()->unique()->sort()->values()->all(),
                'years' => $refs->pluck('date')->filter()
                    ->map(fn ($date) => (int) substr((string) $date, 0, 4))
                    ->unique()->sort()->values()->all(),
            ],
        ];
    }

    /**
     * A worksheet's figures from plain rows - the same marks printable() adds
     * up (the printed parts' marks, or a whole question's own), without
     * building every question as a model. Also returns the ids whose topics
     * count as used, and the question ids on the given page.
     *
     * @return array{0: array<string, mixed>, 1: Collection<int, int>, 2: Collection<int, int>}
     */
    private function tally(Worksheet $worksheet, array $filters, int $page, int $perPage): array
    {
        $rootIds = $worksheet->assessments()->whereHas('question')->pluck('question_id')->values();
        $refs = collect($this->referenceMap($rootIds));
        $types = DB::table('question_bank as qb')
            ->leftJoin('question_categories as c', 'c.id', '=', 'qb.question_categories_id')
            ->whereIn('qb.id', $rootIds)
            ->pluck('c.type', 'qb.id');

        // Every part under these questions, and which question it belongs to.
        $parts = collect();
        $topOf = $rootIds->combine($rootIds)->all();
        for ($level = $rootIds; $level->isNotEmpty(); $level = $children->pluck('id')) {
            $children = DB::table('question_bank')->whereNull('deleted_at')->whereIn('parent_id', $level)->get(['id', 'parent_id', 'marks']);
            foreach ($children as $child) {
                $topOf[$child->id] = $topOf[$child->parent_id];
            }
            $parts = $parts->merge($children);
        }
        $parentIds = $parts->pluck('parent_id')->flip();
        $leaves = $parts->filter(fn ($part) => ! $parentIds->has($part->id))->groupBy(fn ($part) => $topOf[$part->id]);

        $tagged = $this->tagged($filters);
        $taggedIds = array_flip($tagged ?? []);
        $marks = 0.0;
        $printedIds = collect();

        foreach ($rootIds as $id) {
            $printedIds->push($id);
            $picked = ($leaves[$id] ?? collect())->filter(fn ($part) => $tagged === null || isset($taggedIds[$part->id]));
            $printedIds = $printedIds->merge($picked->pluck('id'));

            if ($ref = $refs->get($id)) {
                $marks += isset($leaves[$id]) ? (float) $picked->sum('marks') : (float) $ref->marks;
            }
        }

        return [[
            'total' => $rootIds->count(),
            'marks' => $marks,
            'mcqs' => $types->filter(fn ($type) => $type === 'mcqs')->count(),
            'theory' => $types->filter(fn ($type) => $type === 'theory')->count(),
            'refs' => $refs->values(),
        ], $printedIds, $rootIds->forPage($page, $perPage)->values()];
    }

    /**
     * The questions as they print under these filters.
     *
     * Each question keeps its own text; a `printed` relation holds the parts
     * that print (partsFor), so whole_question / pdf_question give the intro
     * followed by those parts, and `answer` gathers their answers. With no
     * chapter or topic chosen that is every part: the whole question. Set in
     * memory only. Alongside, one reference per question with the printed
     * parts' marks added up.
     *
     * @param  Collection<int, QuestionBank>  $questions
     * @return array{0: Collection<int, QuestionBank>, 1: array<int, object>}
     */
    private function printable(Collection $questions, array $filters): array
    {
        $tagged = $this->tagged($filters);
        $refs = $this->referenceMap($questions->pluck('id'));

        $printed = collect();
        $references = [];

        foreach ($questions as $original) {
            $question = clone $original;
            $all = $original->descendantRows();
            $parts = $this->partsFor($original, $tagged);
            $question->setRelation('printed', $parts);
            // Answers only when loaded (a structure-only load has none).
            $answers = fn (QuestionBank $q) => $q->relationLoaded('answer') ? $q->answer : collect();
            $question->setRelation('answer', $answers($original)->concat($parts->flatMap($answers))->values());
            $printed->push($question);

            if (! $ref = $refs[$original->id] ?? null) {
                continue;
            }

            $parents = $all->pluck('parent_id')->flip();
            $answerable = $parts->filter(fn ($part) => ! $parents->has($part->id));
            $whole = $all->isEmpty() || $parts->count() === $all->count();
            $references[$original->id] = (object) [
                'question_id' => $original->id,
                'date' => $ref->date,
                'paper_no' => $ref->paper_no,
                // "1" for a whole question, "1(a)(i), 1(b)" for some of it.
                'question_no' => $whole ? $ref->question_no : $answerable->pluck('part_ref')->implode(', '),
                'marks' => $all->isEmpty() ? (float) $ref->marks : (float) $answerable->sum('marks'),
                'source' => $ref->source,
            ];
        }

        return [$printed, $references];
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
}

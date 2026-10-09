<?php

namespace App\Imports;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\QuestionCategory;
use App\Models\QuestionableType;
use App\Models\Topic;
use App\Models\WorksheetImport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Reads a past-paper question worksheet into the question bank.
 *
 * Expected columns (header row, any order, case and punctuation insensitive):
 *
 *     Question | Year | Session | Chapter | Topic | Paper No. | Q No. | Marks | Source PDF
 *
 * Each row becomes a question in the bank, linked to a topic, with a past paper
 * citation built from the year, session, paper number and marks - unless the
 * bank already holds that question (same Source PDF and Q No., or the same
 * words when those are missing). Then it is only re-filed under the row's
 * chapter and topic; its text stays as it is, and no second copy is made.
 *
 * A real worksheet runs to thousands of rows, so this reads in chunks and runs
 * on the queue. Within a chunk the three tables are written in bulk rather than
 * row by row: 500 rows cost about six queries instead of fifteen hundred.
 */
class QuestionWorksheetImport implements ToCollection, WithChunkReading, WithHeadingRow
{
    /**
     * Rows per chunk. Large enough to make the bulk inserts worthwhile, small
     * enough that memory stays flat on a 15,000-row file.
     */
    private const CHUNK = 500;

    /**
     * How many bad rows to keep. Past this the run is clearly misconfigured and
     * the rest add nothing but weight to the record.
     */
    private const MAX_FAILURES = 200;

    /**
     * Exam sessions, and the month each one sits in.
     */
    private const SESSIONS = [
        'march' => 3,
        'feb' => 3,
        'feb/march' => 3,
        'may' => 6,
        'june' => 6,
        'may/june' => 6,
        'oct' => 11,
        'nov' => 11,
        'oct/nov' => 11,
    ];

    /** Chapter title (lowercased) => id. */
    private array $chapters = [];

    /** "chapterId|topic title" => id. */
    private array $topics = [];

    /** Questions already handled in this file, by key(): one row each. */
    private array $seen = [];

    private int $imported = 0;

    /** Rows that matched a question already in the bank and re-filed it. */
    private int $updated = 0;

    private int $skipped = 0;

    private int $createdChapters = 0;

    private int $createdTopics = 0;

    private array $failures = [];

    private ?int $theoryCategoryId = null;

    /** File row number of the row being read, for failure messages. */
    private int $line = 1;

    public function __construct(private readonly WorksheetImport $import)
    {
    }

    public function chunkSize(): int
    {
        return self::CHUNK;
    }

    public function collection(Collection $rows): void
    {
        // Chapters and topics must exist before the questions that point at
        // them, and they are few, so they are resolved first and cached.
        $prepared = [];

        foreach ($rows as $row) {
            $this->line++;

            if ($ready = $this->prepare($row->toArray())) {
                $prepared[] = $ready;
            }
        }

        // The same question twice in one file is stored once.
        $unique = [];
        foreach ($prepared as $row) {
            if (isset($this->seen[$row['key']])) {
                $this->skipped++;

                continue;
            }
            $this->seen[$row['key']] = true;
            $unique[] = $row;
        }

        // Questions already in the bank get this row's chapter and topic, and
        // keep their text; only the rest are new.
        $fresh = DB::transaction(fn () => $this->updateExisting($unique));

        if ($fresh !== []) {
            DB::transaction(fn () => $this->writeBatch($fresh));
        }

        $this->flush();
    }

    /**
     * Turns one spreadsheet row into everything needed to store it, or records
     * why it cannot be stored. Returns null for rows to leave alone.
     */
    private function prepare(array $row): ?array
    {
        $question = $this->value($row, ['question']);

        // A blank question line is padding at the end of a sheet, not an error.
        if ($question === null) {
            $this->skipped++;

            return null;
        }

        $topicTitle = $this->value($row, ['topic']);

        if ($topicTitle === null) {
            $this->fail('No topic given, so there is nowhere to file this question.');

            return null;
        }

        $chapterId = $this->chapterFor($this->value($row, ['chapter']));

        if (! $chapterId) {
            $this->fail('No chapter given and the import was not started from one.');

            return null;
        }

        $topicId = $this->topicFor($chapterId, $topicTitle);

        $paperNo = $this->value($row, ['paper no.', 'paper no', 'paper']);
        $questionNo = $this->value($row, ['q no.', 'q no', 'question no', 'question no.']);
        $marks = $this->value($row, ['marks', 'mark']);
        $source = $this->value($row, ['source pdf', 'source']);
        $date = $this->dateFor($this->value($row, ['year']), $this->value($row, ['session']));

        return [
            'chapter_id' => $chapterId,
            'topic_id' => $topicId,
            'source' => $source,
            'question_no' => $questionNo,
            'question' => $question,
            'uuid' => (string) Str::uuid(),
            // What makes two rows the same question: the paper and question
            // number when the row has them, otherwise the words.
            'key' => $source !== null && $questionNo !== null
                ? 'ref|' . mb_strtolower($source) . '|' . $this->ref($questionNo)
                : 'text|' . $this->textHash($question),
            // A citation needs a date, a paper number and marks. A row carrying
            // only some of that still imports, just uncited.
            'citation' => ($date && $paperNo !== null && is_numeric($marks))
                ? [
                    'date' => $date,
                    'paper_no' => $paperNo,
                    'question_no' => $questionNo,
                    'marks' => (float) $marks,
                    'source' => $source,
                ]
                : null,
        ];
    }

    /**
     * Rows for questions already in this course's bank update them instead of
     * adding a second copy: the question (or part) gets the row's chapter and
     * topic, and keeps its text. Returns the rows that are new.
     *
     * A question is recognised by its Source PDF and Q No.:
     *
     *   - a question stored whole (an earlier worksheet import, or a PDF
     *     question with no parts) with that paper and number;
     *   - a part of a PDF-imported question: "2(a)(i)" is that part, "2(a)"
     *     every part with marks under it. A Q No. the PDF's question doesn't
     *     have is reported, not added as a second copy.
     *
     * A row without a Source PDF or Q No. is recognised by its words.
     */
    private function updateExisting(array $rows): array
    {
        $sources = array_values(array_unique(array_filter(array_column($rows, 'source'))));
        $course = $this->import->course_id;

        // Every cited question of these PDFs in this course: "source|question no." => row.
        $cited = $sources === [] ? collect() : DB::table('past_paper_reference as ppr')
            ->join('questionable_type as qt', 'qt.id', '=', 'ppr.questionable_type_id')
            ->join('question_bank as qb', 'qb.id', '=', 'qt.question_id')
            ->whereNull('qb.deleted_at')
            ->where('qb.course_id', $course)
            ->whereIn('ppr.source', $sources)
            ->get(['ppr.source', 'ppr.question_no', 'qb.id as question_id'])
            ->keyBy(fn ($row) => mb_strtolower($row->source) . '|' . $this->ref($row->question_no));

        // The parts under those questions (up to three levels), grouped by the
        // question they belong to; a part with nothing under it is a leaf.
        $parts = collect();
        $level = $cited->pluck('question_id')->unique()->values();
        $topOf = $level->combine($level)->all();
        while ($level->isNotEmpty()) {
            $children = DB::table('question_bank')->whereNull('deleted_at')->whereIn('parent_id', $level)->get(['id', 'parent_id', 'part_ref']);
            foreach ($children as $child) {
                $topOf[$child->id] = $topOf[$child->parent_id];
            }
            $parts = $parts->merge($children);
            $level = $children->pluck('id');
        }
        $parentIds = $parts->pluck('parent_id')->flip();
        $leaves = $parts->filter(fn ($part) => ! $parentIds->has($part->id))->groupBy(fn ($part) => $topOf[$part->id]);

        // Rows without a paper reference: their words, matched in this course.
        $hashes = collect($rows)->filter(fn ($row) => str_starts_with($row['key'], 'text|'))->map(fn ($row) => substr($row['key'], 5));
        $byText = $hashes->isEmpty() ? collect() : DB::table('questions')
            ->join('question_bank as qb', 'qb.id', '=', 'questions.question_bank_id')
            ->whereNull('qb.deleted_at')
            ->where('qb.course_id', $course)
            ->get(['qb.id', 'questions.question'])
            ->keyBy(fn ($row) => $this->textHash($row->question));

        $new = [];

        foreach ($rows as $row) {
            if (str_starts_with($row['key'], 'text|')) {
                $match = $byText->get(substr($row['key'], 5));
                $match ? $this->retag([$match->id], $row) : $new[] = $row;

                continue;
            }

            $no = $this->ref($row['question_no']);
            $source = mb_strtolower($row['source']);
            $exact = $cited->get($source . '|' . $no);

            // Stored whole: that very question.
            if ($exact && ! $parentIds->has($exact->question_id)) {
                $this->retag([$exact->question_id], $row);

                continue;
            }

            // A part of a PDF-imported question.
            $question = preg_match('/^\d+/', $no, $m) ? $cited->get($source . '|' . $m[0]) : null;
            $questionLeaves = $question ? ($leaves[$question->question_id] ?? collect()) : collect();

            if ($questionLeaves->isEmpty()) {
                $new[] = $row;

                continue;
            }

            $named = $questionLeaves->filter(fn ($part) => $this->ref($part->part_ref) === $no || str_starts_with($this->ref($part->part_ref), $no . '('));

            if ($named->isEmpty()) {
                $this->fail("Q No. {$row['question_no']} is not a part of question {$m[0]} imported from {$row['source']}.");

                continue;
            }

            $this->retag($named->pluck('id')->all(), $row);
        }

        return $new;
    }

    /**
     * Files questions already in the bank under a row's chapter and topic:
     * the chapter on the question, the topic through its link (moved if it
     * had one, added if not). The text is left as it is.
     */
    private function retag(array $questionIds, array $row): void
    {
        DB::table('question_bank')->whereIn('id', $questionIds)->update([
            'chapter_id' => $row['chapter_id'],
            'updated_at' => now(),
        ]);

        foreach ($questionIds as $id) {
            $link = DB::table('questionable_type')
                ->where('question_id', $id)
                ->whereIn('questionable_type', [Topic::class, Course::class])
                ->orderBy('id')
                ->first();

            $values = [
                'questionable_type' => Topic::class,
                'questionable_id' => $row['topic_id'],
                'chapter_id' => $row['chapter_id'],
                'updated_at' => now(),
            ];

            $link
                ? DB::table('questionable_type')->where('id', $link->id)->update($values)
                : DB::table('questionable_type')->insert($values + ['question_id' => $id, 'created_at' => now()]);
        }

        $this->updated++;
    }

    /** "3 (b) (ii)" and "3(b)(ii)" are the same part. */
    private function ref(?string $no): string
    {
        return mb_strtolower(preg_replace('/\s+/', '', (string) $no));
    }

    /** Words compared without markup, spacing or case. */
    private function textHash(?string $text): string
    {
        $words = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return md5(mb_strtolower(trim(preg_replace('/\s+/u', ' ', $words))));
    }

    /**
     * Writes a chunk's questions, links and citations in three bulk inserts.
     *
     * Ids are mapped back rather than assumed: questions by the uuid generated
     * here, links by the question they belong to — every row creates its own
     * question, so that is unique within the batch.
     */
    private function writeBatch(array $rows): void
    {
        $now = now();
        $categoryId = $this->theoryCategory();

        DB::table('question_bank')->insert(array_map(fn ($row) => [
            'uuid' => $row['uuid'],
            'course_id' => $this->import->course_id,
            'chapter_id' => $row['chapter_id'],
            'question_categories_id' => $categoryId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $rows));

        $questionIds = DB::table('question_bank')
            ->whereIn('uuid', array_column($rows, 'uuid'))
            ->pluck('id', 'uuid');

        // The words live in `questions`, one row each.
        DB::table('questions')->insert(array_map(fn ($row) => [
            'uuid' => (string) Str::uuid(),
            'question_bank_id' => $questionIds[$row['uuid']],
            'question' => $row['question'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $rows));

        $links = [];

        foreach ($rows as $row) {
            $links[] = [
                'chapter_id' => $row['chapter_id'],
                'question_id' => $questionIds[$row['uuid']],
                'questionable_type' => Topic::class,
                'questionable_id' => $row['topic_id'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('questionable_type')->insert($links);

        $linkIds = DB::table('questionable_type')
            ->whereIn('question_id', array_column($links, 'question_id'))
            ->pluck('id', 'question_id');

        $citations = [];

        foreach ($rows as $row) {
            if (! $row['citation']) {
                continue;
            }

            $citations[] = [
                'uuid' => (string) Str::uuid(),
                'questionable_type_id' => $linkIds[$questionIds[$row['uuid']]],
                'date' => $row['citation']['date'],
                'paper_no' => $row['citation']['paper_no'],
                'question_no' => $row['citation']['question_no'],
                'marks' => $row['citation']['marks'],
                'source' => $row['citation']['source'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($citations !== []) {
            DB::table('past_paper_reference')->insert($citations);
        }

        $this->imported += count($rows);
    }

    /**
     * Reads a cell by any of its accepted header spellings. Laravel Excel
     * lowercases headings and turns spaces into underscores, so both shapes
     * are checked. Returns null for anything blank.
     */
    private function value(array $row, array $names): ?string
    {
        foreach ($names as $name) {
            foreach ([$name, str_replace([' ', '.'], ['_', ''], $name)] as $key) {
                if (array_key_exists($key, $row) && $row[$key] !== null) {
                    $value = trim((string) $row[$key]);

                    if ($value !== '') {
                        return $value;
                    }
                }
            }
        }

        return null;
    }

    /**
     * The chapter a row belongs to: matched by title within the course, created
     * when new, falling back to the chapter the import was started from.
     */
    private function chapterFor(?string $title): ?int
    {
        if ($title === null) {
            return $this->import->chapter_id;
        }

        $key = mb_strtolower($title);

        if (isset($this->chapters[$key])) {
            return $this->chapters[$key];
        }

        $chapter = Chapter::where('course_id', $this->import->course_id)
            ->whereRaw('LOWER(title) = ?', [$key])
            ->first();

        if (! $chapter) {
            $chapter = Chapter::create([
                'course_id' => $this->import->course_id,
                'title' => $title,
                'chapter_number' => (int) Chapter::where('course_id', $this->import->course_id)->max('chapter_number') + 1,
                'description' => 'Imported from a question worksheet.',
            ]);

            $this->createdChapters++;
        }

        return $this->chapters[$key] = $chapter->id;
    }

    private function topicFor(int $chapterId, string $title): int
    {
        $key = $chapterId . '|' . mb_strtolower($title);

        if (isset($this->topics[$key])) {
            return $this->topics[$key];
        }

        $topic = Topic::where('chapter_id', $chapterId)
            ->whereRaw('LOWER(title) = ?', [mb_strtolower($title)])
            ->first();

        if (! $topic) {
            $topic = Topic::create([
                'chapter_id' => $chapterId,
                'title' => $title,
            ]);

            $this->createdTopics++;
        }

        return $this->topics[$key] = $topic->id;
    }

    /**
     * "2018" + "Oct/Nov" becomes the first of that month. Papers are cited by
     * session rather than by day, so the day is arbitrary but consistent.
     */
    private function dateFor(?string $year, ?string $session): ?string
    {
        if (! $year || ! is_numeric($year)) {
            return null;
        }

        $month = self::SESSIONS[mb_strtolower(trim((string) $session))] ?? null;

        if (! $month) {
            return null;
        }

        return Carbon::createFromDate((int) $year, $month, 1)->toDateString();
    }

    private function fail(string $reason): void
    {
        $this->skipped++;

        if (count($this->failures) < self::MAX_FAILURES) {
            $this->failures[] = ['row' => $this->line, 'reason' => $reason];
        }
    }

    /**
     * Writes progress after each chunk, so the page can watch it move.
     */
    private function flush(): void
    {
        $this->import->forceFill([
            'status' => 'running',
            // New questions plus existing ones re-filed under this file's chapter and topic.
            'imported_rows' => $this->imported + $this->updated,
            'skipped_rows' => $this->skipped,
            'created_topics' => $this->createdTopics,
            'created_chapters' => $this->createdChapters,
            'failures' => $this->failures ?: null,
        ])->save();
    }

    private function theoryCategory(): int
    {
        return $this->theoryCategoryId ??= QuestionCategory::firstOrCreate(['type' => 'theory'])->id;
    }
}

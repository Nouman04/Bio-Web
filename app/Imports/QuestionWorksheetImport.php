<?php

namespace App\Imports;

use App\Models\Chapter;
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
 * citation built from the year, session, paper number and marks.
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

    /** Signatures of citations already stored. */
    private array $seen = [];

    private int $imported = 0;

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

        if ($prepared === []) {
            $this->flush();

            return;
        }

        // One query tells us which of these are already stored.
        $this->preloadSeen($prepared);

        $fresh = [];

        foreach ($prepared as $row) {
            if (isset($this->seen[$row['signature']])) {
                $this->skipped++;

                continue;
            }

            // Guards against the same citation appearing twice in one file.
            $this->seen[$row['signature']] = true;
            $fresh[] = $row;
        }

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
            'question' => $question,
            'uuid' => (string) Str::uuid(),
            'signature' => $this->signature($topicId, $question, $paperNo, $questionNo, $source),
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
            'chapter_id' => $row['chapter_id'],
            'question_categories_id' => $categoryId,
            'question' => $row['question'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $rows));

        $questionIds = DB::table('question_bank')
            ->whereIn('uuid', array_column($rows, 'uuid'))
            ->pluck('id', 'uuid');

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
     * Loads the citations already stored for the topics in this chunk, so the
     * duplicate check can answer from memory.
     */
    private function preloadSeen(array $rows): void
    {
        $topicIds = array_values(array_unique(array_column($rows, 'topic_id')));

        if ($topicIds === []) {
            return;
        }

        DB::table('questionable_type')
            ->where('questionable_type', Topic::class)
            ->whereIn('questionable_id', $topicIds)
            ->join('question_bank', 'question_bank.id', '=', 'questionable_type.question_id')
            ->leftJoin('past_paper_reference', 'past_paper_reference.questionable_type_id', '=', 'questionable_type.id')
            ->select([
                'questionable_type.questionable_id',
                'question_bank.question',
                'past_paper_reference.paper_no',
                'past_paper_reference.question_no',
                'past_paper_reference.source',
            ])
            ->cursor()
            ->each(function ($row) {
                $this->seen[$this->signature(
                    (int) $row->questionable_id,
                    (string) $row->question,
                    $row->paper_no,
                    $row->question_no,
                    $row->source
                )] = true;
            });
    }

    private function signature(int $topicId, string $question, ?string $paperNo, ?string $questionNo, ?string $source): string
    {
        return $topicId . '|' . md5($question) . '|' . $paperNo . '|' . $questionNo . '|' . $source;
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
            'imported_rows' => $this->imported,
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

<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;

class QuestionBank extends Model
{
    use SoftDeletes, HasUuid, Searchable;

    protected $table = 'question_bank';

    protected $fillable = [
        'course_id',
        'chapter_id',
        'parent_id',
        'part_ref',
        'marks',
        'question_categories_id',
        // Not a column: the text is stored in `questions` (see below).
        'question',
        'difficulty_level',
    ];

    protected $casts = [
        'marks' => 'float',
    ];

    /**
     * Eager loads for a question with up to three levels of parts below it
     * (3 -> 3(b) -> 3(b)(i)), each with its text and answer.
     */
    public const TREE = [
        'text', 'answer',
        'children.text', 'children.answer',
        'children.children.text', 'children.children.answer',
        'children.children.children.text', 'children.children.children.answer',
    ];

    /** Text set through `question`, written to `questions` once the row is saved. */
    protected ?string $pendingText = null;

    /**
     * A question with a chapter belongs to that chapter's course, so callers
     * that only set the chapter still get course_id filled in.
     */
    protected static function booted(): void
    {
        static::saving(function (QuestionBank $question) {
            if ($question->chapter_id && $question->isDirty('chapter_id')) {
                $question->course_id = Chapter::whereKey($question->chapter_id)->value('course_id');
            }
        });

        // The text lives in `questions`: one row while there is any, none
        // when it is blank, so no empty text rows are kept.
        static::saved(function (QuestionBank $question) {
            if ($question->pendingText === null) {
                return;
            }

            $text = $question->pendingText;
            $question->pendingText = null;
            $question->unsetRelation('text');

            if (trim(strip_tags($text, '<img><table>')) === '') {
                Question::where('question_bank_id', $question->id)->delete();

                return;
            }

            Question::updateOrCreate(['question_bank_id' => $question->id], ['question' => $text]);
        });

        // A soft delete never fires the foreign-key cascade, so a question
        // takes its parts with it here.
        static::deleting(function (QuestionBank $question) {
            $question->children()->get()->each->delete();
        });
    }

    /**
     * `question` reads and writes the text kept in `questions`, so every
     * caller that creates, updates or prints a question works unchanged.
     */
    public function getQuestionAttribute(): string
    {
        if ($this->pendingText !== null) {
            return $this->pendingText;
        }

        return $this->exists ? (string) $this->text?->question : '';
    }

    public function setQuestionAttribute(?string $value): void
    {
        $this->pendingText = (string) $value;
    }

    /** The row's text, when it has any. */
    public function text(): HasOne
    {
        return $this->hasOne(Question::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** The question or part this one sits under: 3(b) for 3(b)(i). */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** The parts directly under this one, in paper order. */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('id');
    }

    /**
     * Every part below this one, in paper order (each row is followed by its
     * own parts, the order they were printed and created in). Uses the loaded
     * `children`, so load self::TREE first when reading many.
     *
     * @return \Illuminate\Support\Collection<int, QuestionBank>
     */
    public function descendantRows(): \Illuminate\Support\Collection
    {
        return $this->children->flatMap(fn (QuestionBank $child) => collect([$child])->merge($child->descendantRows()));
    }

    /**
     * What a chapter's question bank lists, one row per question or part that
     * has text: rows tagged to the chapter, the question and lead-ins above
     * them, and the course's rows nothing has been tagged under yet.
     */
    public function scopeForChapter($query, Chapter $chapter)
    {
        $rows = fn () => DB::table('question_bank')->whereNull('deleted_at');
        $parentsOf = fn ($children) => $rows()->whereIn('id', $children->whereNotNull('parent_id')->select('parent_id'))->select('id');
        $here = fn () => $rows()->where('chapter_id', $chapter->id);
        $tagged = fn () => $rows()->whereNotNull('chapter_id');

        return $query->whereHas('text')->where(fn ($q) => $q
            ->where('question_bank.chapter_id', $chapter->id)
            // The question and lead-ins above a part tagged here.
            ->orWhereIn('question_bank.id', $parentsOf($here()))
            ->orWhereIn('question_bank.id', $parentsOf($rows()->whereIn('id', $parentsOf($here()))))
            // Nothing tagged at or below it yet.
            ->orWhere(fn ($q) => $q
                ->whereNull('question_bank.chapter_id')
                ->where('question_bank.course_id', $chapter->course_id)
                ->whereNotIn('question_bank.id', $parentsOf($tagged()))
                ->whereNotIn('question_bank.id', $parentsOf($rows()->whereIn('id', $parentsOf($tagged()))))));
    }

    /**
     * Rich-text markup made safe to print unescaped: only formatting tags
     * survive, with no event handlers or script URLs.
     */
    public static function sanitize(?string $html): string
    {
        // ponytail: tag allowlist + regex; swap for a real sanitizer if question HTML ever comes from students.
        $html = strip_tags((string) $html, '<p><br><strong><b><em><i><u><s><sub><sup><span><ol><ul><li><table><tbody><thead><tr><td><th><img><h2><h3><blockquote>');
        $html = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);

        return preg_replace('/(href|src)\s*=\s*(["\']?)\s*javascript:[^"\'\s>]*\2/i', '$1=""', $html);
    }

    /**
     * The question's markup, safe to print unescaped on student pages: only
     * formatting tags survive, with no event handlers or script URLs.
     */
    public function getSafeQuestionAttribute(): string
    {
        return static::sanitize($this->whole_question);
    }

    /**
     * The question as printed: its own text followed by every part below it,
     * in paper order. `question` itself is just this row's text, which is
     * what the edit form loads and saves.
     *
     * A caller can set a `printed` relation to choose which parts below it
     * print (a worksheet filtered by chapter prints only some).
     */
    public function getWholeQuestionAttribute(): string
    {
        if (! $this->exists) {
            return $this->question;
        }

        $parts = $this->relationLoaded('printed') ? $this->getRelation('printed') : $this->descendantRows();

        return $this->question . $parts->map(fn (QuestionBank $part) => $part->question)->implode('');
    }

    /**
     * safe_question for the PDF renderer: our own stored images are read
     * straight off disk. Fetching them over HTTP would deadlock the
     * single-threaded dev server that is busy rendering this very PDF.
     */
    public function getPdfQuestionAttribute(): string
    {
        return preg_replace_callback('/src="[^"]*\/storage\/([^"]+)"/', function ($m) {
            $path = storage_path('app/public/' . rawurldecode($m[1]));

            return is_file($path) ? 'src="file:///' . str_replace('\\', '/', $path) . '"' : $m[0];
        }, $this->safe_question);
    }

    /**
     * The whole question as words alone, for pickers and search results.
     */
    public function getPlainQuestionAttribute(): string
    {
        return static::plain($this->whole_question);
    }

    /**
     * Just this row's words, for a list that shows every question and part
     * as its own row. A past-paper part's printed label ("3 (b) (i)") is
     * left off, since the row stands on its own there.
     */
    public function getPlainTextAttribute(): string
    {
        $text = static::plain($this->question);

        return $this->part_ref ? preg_replace('/^(\d{1,2}\s*)?(\(\s*[a-z]+\s*\)\s*)*/i', '', $text) : $text;
    }

    /**
     * Markup as words: paragraphs and cells kept apart, dotted answer lines
     * (space to write in) dropped.
     */
    public static function plain(?string $html): string
    {
        $html = preg_replace('/<(\/p|br|\/li|\/td|\/tr|\/h\d)\b[^>]*>/i', ' ', (string) $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[.…]{4,}/u', ' ', $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * Everywhere this question has been linked to a piece of content — a
     * topic, summary, note, diagram or video lesson.
     */
    public function questionables(): HasMany
    {
        return $this->hasMany(QuestionableType::class, 'question_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(QuestionCategory::class, 'question_categories_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function answer(): HasMany
    {
        return $this->hasMany(QuestionAnswer::class);
    }

    /**
     * Every place this question has been attached to an assessment — a
     * flashcard deck, worksheet or quiz.
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'question_id');
    }

    public function diagrams(): HasMany
    {
        return $this->hasMany(Diagram::class);
    }

    /**
     * Topics this question is referenced in.
     */
    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'topics_questions', 'question_id', 'topics_id')
            ->withPivot('referenced_date')
            ->withTimestamps();
    }

    public function quizQuestions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class);
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'difficulty_level' => $this->difficulty_level,
        ];
    }
}

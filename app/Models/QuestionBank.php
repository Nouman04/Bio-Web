<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

class QuestionBank extends Model
{
    use SoftDeletes, HasUuid, Searchable;

    protected $table = 'question_bank';

    protected $fillable = [
        'chapter_id',
        'question_categories_id',
        'question',
        'difficulty_level',
    ];

    /**
     * The question as words alone.
     *
     * The text is written in a rich text editor, so the stored value is markup.
     * A picker label, a search result and a confirmation dialog all want the
     * words rather than the tags around them.
     */
    /**
     * The question's markup, safe to print unescaped on student pages: only
     * formatting tags survive, with no event handlers or script URLs.
     */
    public function getSafeQuestionAttribute(): string
    {
        // ponytail: tag allowlist + regex; swap for a real sanitizer if question HTML ever comes from students.
        $html = strip_tags((string) $this->question, '<p><br><strong><b><em><i><u><s><sub><sup><span><ol><ul><li><table><tbody><thead><tr><td><th><img><h2><h3><blockquote>');
        $html = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);

        return preg_replace('/(href|src)\s*=\s*(["\']?)\s*javascript:[^"\'\s>]*\2/i', '$1=""', $html);
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

    public function getPlainQuestionAttribute(): string
    {
        // Break at block tags so paragraphs and table cells don't run together.
        $html = preg_replace('/<(\/p|br|\/li|\/td|\/tr|\/h\d)\b[^>]*>/i', ' ', (string) $this->question);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Dotted answer lines are space to write in, not words.
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

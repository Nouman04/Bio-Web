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
    public function getPlainQuestionAttribute(): string
    {
        $text = html_entity_decode(strip_tags((string) $this->question), ENT_QUOTES | ENT_HTML5, 'UTF-8');

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

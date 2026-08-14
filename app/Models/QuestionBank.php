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
        'topic_id',
        'question_categories_id',
        'question',
        'difficulty_level',
    ];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
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

    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class, 'question_id');
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

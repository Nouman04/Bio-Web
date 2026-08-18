<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Laravel\Scout\Searchable;

class Quiz extends Model
{
    use SoftDeletes, HasUuid, Searchable;

    protected $fillable = [
        'title',
        'description',
        'type',
        'duration',
        'passing_score',
        'shuffle_questions',
        'status',
    ];

    protected $casts = [
        'shuffle_questions' => 'boolean',
        'duration' => 'integer',
        'passing_score' => 'integer',
    ];

    /**
     * Quizzes are reached through a chapter, so a quiz has at most one chapter
     * even though the pivot could hold several.
     */
    public function firstChapter(): ?Chapter
    {
        return $this->chapters->first();
    }

    /**
     * The questions on this quiz, in the order they are answered. They hang off
     * the quizzes_chapters rows rather than the quiz itself.
     */
    public function questions(): HasManyThrough
    {
        return $this->hasManyThrough(
            QuizQuestion::class,
            QuizChapter::class,
            'quizz_id',
            'quiz_chapter_id'
        )->orderBy('quiz_questions.order');
    }

    public function quizChapters(): HasMany
    {
        return $this->hasMany(QuizChapter::class, 'quizz_id');
    }

    /**
     * Chapters included in this quiz (via quizzes_chapters pivot).
     */
    public function chapters(): BelongsToMany
    {
        return $this->belongsToMany(Chapter::class, 'quizzes_chapters', 'quizz_id', 'chapter_id')
            ->withTimestamps();
    }

    public function userAttempts(): HasMany
    {
        return $this->hasMany(QuizUserAttempt::class);
    }

    /**
     * Questions attached to this assessment, in order.
     */
    public function assessments()
    {
        return $this->morphMany(Assessment::class, "assessmentable")->orderBy("order");
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
        ];
    }
}

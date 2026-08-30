<?php

namespace App\Models;

use App\Models\Concerns\HasStatus;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Laravel\Scout\Searchable;

class Quiz extends Model
{
    use SoftDeletes, HasUuid, HasStatus, Searchable;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'type',
        'duration',
        'passing_score',
        'shuffle_questions',
        // Not a column: HasStatus writes it to the statuses table.
        'status',
        'marking',
    ];

    /**
     * Who marks the written answers. `instructor` sends the paper to whoever
     * owns the course; `self` hands it straight back with the model answers,
     * with nobody notified and nothing queued for marking.
     */
    public const MARKING = [
        'instructor' => 'Instructor marks the paper',
        'self' => 'No marking — the student judges their own answers',
    ];

    /**
     * Whether this quiz has written answers at all. An MCQ paper marks itself,
     * so the marking mode never applies to one.
     */
    public function getIsMarkableAttribute(): bool
    {
        return in_array($this->type, ['theory', 'mixed'], true);
    }

    /**
     * Whether the student marks their own written answers.
     */
    public function isSelfMarked(): bool
    {
        return $this->is_markable && $this->marking === 'self';
    }

    protected $casts = [
        'shuffle_questions' => 'boolean',
        'duration' => 'integer',
        'passing_score' => 'integer',
    ];

    /**
     * Quizzes are reached through a chapter, so a quiz has at most one chapter
     * even though the pivot could hold several.
     */

    /**
     * A plain-text opening line from the description, for listings and cards. The
     * description is Quill HTML, so entities are decoded before trimming.
     */
    public function getExcerptAttribute(): string
    {
        $text = html_entity_decode(strip_tags((string) $this->description), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', $text)), 160);
    }

    /**
     * The course this quiz is set for. It decides which questions the builder
     * offers, so it is a column of its own rather than something inferred from
     * whichever chapter happens to be attached.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

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

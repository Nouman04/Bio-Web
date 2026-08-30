<?php

namespace App\Models;

use App\Models\Concerns\HasStatus;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

/**
 * One student sitting one quiz.
 *
 * An MCQ attempt marks itself the moment it is submitted. Written answers either
 * wait on an instructor — `pending_review` — or come straight back to the student
 * with the model answers beside them, which is `self_marked`. Which of the two
 * depends on the quiz's marking mode.
 */
class QuizUserAttempt extends Model
{
    use SoftDeletes, HasUuid, HasStatus, Searchable;

    /**
     * Statuses that mean the student is finished, whether or not it is marked.
     */
    public const CLOSED = ['submitted', 'pending_review', 'self_marked', 'graded', 'expired'];

    protected $fillable = [
        'quiz_id',
        'user_id',
        // Not a column: HasStatus writes it to the statuses table.
        'status',
        'started_at',
        'expires_at',
        'submitted_at',
        'earned_marks',
        'total_marks',
        'passed',
        'graded_by',
        'graded_at',
        'feedback',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
            'earned_marks' => 'decimal:2',
            'total_marks' => 'decimal:2',
            'passed' => 'boolean',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function answers(): HasMany
    {
        // The column is `attempt_id`; the old relation named a column that does
        // not exist, so it always came back empty.
        return $this->hasMany(QuizAttemptAnswer::class, 'attempt_id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereStatus('in_progress');
    }

    public function scopeAwaitingReview(Builder $query): Builder
    {
        return $query->whereStatus('pending_review');
    }

    /**
     * Whether the clock has run out. A quiz with no duration never expires.
     */
    public function hasExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Seconds left, floored at zero. Null when the quiz is untimed.
     */
    public function secondsRemaining(): ?int
    {
        if (! $this->expires_at) {
            return null;
        }

        // Carbon returns a float; the clock only ever shows whole seconds.
        return (int) max(0, floor(now()->diffInSeconds($this->expires_at, false)));
    }

    public function isClosed(): bool
    {
        return in_array($this->status, self::CLOSED, true);
    }

    /**
     * The score as a percentage, for the report header.
     */
    public function getPercentAttribute(): int
    {
        if (! $this->total_marks || (float) $this->total_marks <= 0) {
            return 0;
        }

        return (int) round((float) $this->earned_marks / (float) $this->total_marks * 100);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'quiz' => $this->quiz?->title,
            'user' => $this->user?->name,
        ];
    }
}

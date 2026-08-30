<?php

namespace App\Models\Concerns;

use App\Models\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Keeps a record's status in the shared `statuses` table rather than in a
 * column of its own.
 *
 * Reading and writing stay exactly as they were — `$chapter->status`,
 * `$quiz->update(['status' => 'published'])`, `Quiz::create([... 'status' =>
 * ...])` — because the accessor and mutator stand in for the column that used
 * to be there. Only *querying* changes: a status lives on another table now, so
 * `where('status', …)` becomes `whereStatus(…)`.
 *
 *     Quiz::whereStatus('published')->get();
 *     $attempts->whereStatus(QuizUserAttempt::CLOSED);
 *     Chapter::orderByStatus('desc');
 *
 * The status row is written after the model is saved, so a status given at
 * creation lands as soon as there is a record to hang it on.
 */
trait HasStatus
{
    /**
     * A status set but not yet written. Declared, so assigning to it never
     * reaches Eloquent's attribute bag.
     */
    protected ?string $pendingStatus = null;

    public static function bootHasStatus(): void
    {
        static::saved(function ($model) {
            $model->flushPendingStatus();
        });

        // A soft-deleted record keeps its status — it is restorable, and the
        // status is part of what would come back. Only a permanent delete
        // takes the row with it.
        static::forceDeleted(function ($model) {
            $model->statusRecord()->delete();
        });
    }

    /**
     * The status row is loaded with every record — it is read far more often
     * than not, and a listing that fetched it row by row would be a query per
     * row — and appended under the name the column had, so an API response or a
     * DataTables payload is unchanged.
     *
     * Set here rather than as trait properties: Model declares $with and
     * $appends itself, and a trait cannot redeclare them.
     */
    public function initializeHasStatus(): void
    {
        $this->with = array_values(array_unique(array_merge($this->with, ['statusRecord'])));
        $this->appends = array_values(array_unique(array_merge($this->appends, ['status'])));
    }

    public function statusRecord(): MorphOne
    {
        return $this->morphOne(Status::class, 'statusable');
    }

    /**
     * The record's status, or null when it has never been given one.
     */
    public function getStatusAttribute(): ?string
    {
        if ($this->pendingStatus !== null) {
            return $this->pendingStatus;
        }

        if (! $this->relationLoaded('statusRecord')) {
            $this->setRelation('statusRecord', $this->exists ? $this->statusRecord()->first() : null);
        }

        return $this->getRelation('statusRecord')?->status;
    }

    /**
     * Held until the record is saved: a status set on a model that does not
     * exist yet has nothing to attach to.
     */
    public function setStatusAttribute(?string $status): void
    {
        $this->pendingStatus = $status;
    }

    /**
     * Writes the held status, if there is one. Called for you on save.
     */
    public function flushPendingStatus(): void
    {
        if ($this->pendingStatus === null) {
            return;
        }

        $status = $this->pendingStatus;
        $this->pendingStatus = null;

        $this->statusRecord()->updateOrCreate([], ['status' => $status]);
        $this->unsetRelation('statusRecord');
    }

    /**
     * Records whose status is one of these.
     *
     * @param  string|array<int, string>  $status
     */
    public function scopeWhereStatus(Builder $query, string|array $status): Builder
    {
        return $query->whereHas(
            'statusRecord',
            fn (Builder $inner) => $inner->whereIn('status', (array) $status)
        );
    }

    /**
     * Records whose status is anything but these — including records that have
     * no status at all.
     *
     * @param  string|array<int, string>  $status
     */
    public function scopeWhereStatusNot(Builder $query, string|array $status): Builder
    {
        return $query->whereDoesntHave(
            'statusRecord',
            fn (Builder $inner) => $inner->whereIn('status', (array) $status)
        );
    }

    /**
     * Orders by status. A correlated subquery rather than a join, so it can be
     * added to a query that is already paged and counted.
     */
    public function scopeOrderByStatus(Builder $query, string $direction = 'asc'): Builder
    {
        $model = $query->getModel();

        return $query->orderBy(
            Status::query()
                ->select('status')
                ->whereColumn('statuses.statusable_id', $model->getTable() . '.' . $model->getKeyName())
                ->where('statuses.statusable_type', $model->getMorphClass())
                ->limit(1),
            $direction
        );
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A worksheet's question paper or mark scheme, built on the queue by
 * GenerateWorksheetPdf, with its progress and the finished file.
 */
class WorksheetPdf extends Model
{
    use HasUuid;

    public const TYPES = ['paper', 'mark-scheme'];

    protected $fillable = [
        'worksheet_id',
        'type',
        'status',
        'progress',
        'stage',
        'path',
        'error',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function worksheet(): BelongsTo
    {
        return $this->belongsTo(Worksheet::class);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['completed', 'failed'], true);
    }

    /** What the job is doing, as the page shows it. */
    public function step(int $progress, string $stage): void
    {
        $this->forceFill(['progress' => min(100, max(0, $progress)), 'stage' => $stage])->save();
    }
}

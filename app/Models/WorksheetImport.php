<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One run of the question-worksheet importer, and how far it got.
 */
class WorksheetImport extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'course_id',
        'chapter_id',
        'filename',
        'path',
        'status',
        'total_rows',
        'imported_rows',
        'skipped_rows',
        'created_topics',
        'created_chapters',
        'failures',
        'error',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'failures' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['completed', 'failed'], true);
    }

    /**
     * How far through, as a percentage. Total is only known once the file has
     * been counted, so this reads 0 until then.
     */
    public function getPercentAttribute(): int
    {
        if ($this->total_rows < 1) {
            return $this->status === 'completed' ? 100 : 0;
        }

        $done = $this->imported_rows + $this->skipped_rows;

        return (int) min(100, round($done / $this->total_rows * 100));
    }
}

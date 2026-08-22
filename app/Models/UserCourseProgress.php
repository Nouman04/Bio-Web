<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A cached percentage — for a chapter when `chapter_id` is set, for the whole
 * course when it is null. Derived data: every row can be rebuilt from
 * user_module_progress, and RecalculateProgressJob does exactly that.
 */
class UserCourseProgress extends Model
{
    use HasUuid;

    protected $table = 'user_course_progress';

    protected $fillable = [
        'user_id',
        'course_id',
        'chapter_id',
        'chapter_key',
        'progress',
        'completed_weight',
        'total_weight',
        'recalculated_at',
    ];

    protected function casts(): array
    {
        return [
            'progress' => 'float',
            'completed_weight' => 'integer',
            'total_weight' => 'integer',
            'recalculated_at' => 'datetime',
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

    /**
     * Whether this row is the course total rather than one chapter's.
     */
    public function isCourseLevel(): bool
    {
        return $this->chapter_id === null;
    }
}

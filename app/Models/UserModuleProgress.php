<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * How far one user has got with one module. Written the first time they touch
 * it and updated as they go; `is_completed` is the flag the percentages count.
 */
class UserModuleProgress extends Model
{
    use HasUuid;

    protected $table = 'user_module_progress';

    protected $fillable = [
        'user_id',
        'course_module_id',
        'is_completed',
        'progress',
        'completed_via',
        'completed_at',
    ];

    /**
     * The column defaults, restated here so a row created without them comes
     * back hydrated rather than with nulls the database will fill in later.
     */
    protected $attributes = [
        'is_completed' => false,
        'progress' => 0,
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'progress' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }
}

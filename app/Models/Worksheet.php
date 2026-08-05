<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Worksheet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'course_id',
        'created_by',
        'title',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function worksheetChapters(): HasMany
    {
        return $this->hasMany(WorksheetChapter::class);
    }
}

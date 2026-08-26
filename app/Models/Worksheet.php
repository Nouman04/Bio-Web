<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Worksheet extends Model
{
    use SoftDeletes, HasUuid, Searchable;

    protected $fillable = [
        'course_id',
        'created_by',
        'title',
        'filters',
    ];

    /**
     * What was asked for when the worksheet was built. A topic that had
     * nothing to contribute is still recorded here, because the cover page
     * lists everything that was asked for, not only what answered.
     */
    protected $casts = [
        'filters' => 'array',
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
        ];
    }
}

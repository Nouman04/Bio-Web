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

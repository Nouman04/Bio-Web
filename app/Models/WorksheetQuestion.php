<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class WorksheetQuestion extends Model
{
    use SoftDeletes, HasUuid, Searchable;

    protected $table = 'worksheets_questions';

    protected $fillable = [
        'worksheets_chapters_id',
        'topics_id',
    ];

    public function worksheetChapter(): BelongsTo
    {
        return $this->belongsTo(WorksheetChapter::class, 'worksheets_chapters_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topics_id');
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'topic' => $this->topic?->title,
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorksheetQuestion extends Model
{
    use SoftDeletes;

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
}

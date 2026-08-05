<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorksheetChapter extends Model
{
    use SoftDeletes;

    protected $table = 'worksheets_chapters';

    protected $fillable = [
        'chapter_id',
        'worksheet_id',
    ];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function worksheet(): BelongsTo
    {
        return $this->belongsTo(Worksheet::class);
    }

    /**
     * Questions in this worksheet chapter.
     * - Question Paper: load questions only
     * - Mark Scheme: load questions with their answers (eager load questionBank.answer)
     */
    public function worksheetQuestions(): HasMany
    {
        return $this->hasMany(WorksheetQuestion::class, 'worksheets_chapters_id');
    }
}

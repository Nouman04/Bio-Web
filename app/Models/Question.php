<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The words of one question_bank row - a question or a past-paper part.
 *
 * Kept apart so a row with no text of its own (a question whose first line
 * is "3 (a) ...", all of which belongs to part (a)) stores nothing. Read and
 * written through QuestionBank's `question` attribute.
 */
class Question extends Model
{
    use HasUuid;

    protected $fillable = [
        'question_bank_id',
        'question',
    ];

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }
}

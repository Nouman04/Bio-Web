<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Flashcard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'note_id',
        'question_id',
        'question_answer_id',
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }

    public function questionAnswer(): BelongsTo
    {
        return $this->belongsTo(QuestionAnswer::class, 'question_answer_id');
    }
}

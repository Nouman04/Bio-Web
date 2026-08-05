<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionAnswer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'question_bank_id',
        'question_option_id',
        'expected_answer',
        'description',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_bank_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }

    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class, 'question_answer_id');
    }
}

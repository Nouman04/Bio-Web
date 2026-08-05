<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizQuestion extends Model
{
    use SoftDeletes;

    protected $table = 'quiz_questions';

    protected $fillable = [
        'quiz_chapter_id',
        'question_bank_id',
        'marks',
    ];

    protected $casts = [
        'marks' => 'decimal:2',
    ];

    public function quizChapter(): BelongsTo
    {
        return $this->belongsTo(QuizChapter::class, 'quiz_chapter_id');
    }

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_bank_id');
    }

    public function attemptAnswers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class, 'quizzes_question_id');
    }
}

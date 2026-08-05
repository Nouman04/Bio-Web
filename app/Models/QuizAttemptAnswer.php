<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttemptAnswer extends Model
{
    use SoftDeletes;

    protected $table = 'quiz_attempt_answers';

    protected $fillable = [
        'quizzes_question_id',
        'attempt_id',
        'selected_option',
        'is_correct',
        'answer_content',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function quizQuestion(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'quizzes_question_id');
    }

    public function userAttempt(): BelongsTo
    {
        return $this->belongsTo(QuizUserAttempt::class, 'attempt_id');
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option');
    }
}

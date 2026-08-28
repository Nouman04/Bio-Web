<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class QuizAttemptAnswer extends Model
{
    use SoftDeletes, HasUuid, Searchable;

    protected $table = 'quiz_attempt_answers';

    protected $fillable = [
        'quizzes_question_id',
        'attempt_id',
        'selected_option',
        'is_correct',
        'answer_content',
        'marks_awarded',
        'feedback',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'marks_awarded' => 'decimal:2',
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

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'answer_content' => $this->answer_content,
        ];
    }
}

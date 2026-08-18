<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class QuizQuestion extends Model
{
    use SoftDeletes, HasUuid, Searchable;

    protected $table = 'quiz_questions';

    protected $fillable = [
        'quiz_chapter_id',
        'question_bank_id',
        'order',
        'marks',
    ];

    protected $casts = [
        'marks' => 'decimal:2',
        'order' => 'integer',
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

    /**
     * Get the indexable data array for the model. Indexes the linked
     * question's text since this join record has none of its own.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'question' => $this->questionBank?->question,
        ];
    }
}

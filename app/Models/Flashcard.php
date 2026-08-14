<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Flashcard extends Model
{
    use SoftDeletes, HasUuid, Searchable;

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

    /**
     * Get the indexable data array for the model. Flashcards have no text of
     * their own, so index the question they're built from.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question?->question,
        ];
    }
}

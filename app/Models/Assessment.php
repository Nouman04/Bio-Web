<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Assessment extends Model
{
    use HasUuid;

    protected $fillable = [
        'assessmentable_id',
        'assessmentable_type',
        'question_id',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * What this question was attached to: a flashcard deck, worksheet or quiz.
     */
    public function assessmentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }
}

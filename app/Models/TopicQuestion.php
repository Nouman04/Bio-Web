<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicQuestion extends Model
{
    use SoftDeletes;

    protected $table = 'topics_questions';

    protected $fillable = [
        'topics_id',
        'question_id',
        'referenced_date',
    ];

    protected $casts = [
        'referenced_date' => 'date',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topics_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }
}

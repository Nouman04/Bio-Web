<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizChapter extends Model
{
    use SoftDeletes;

    protected $table = 'quizzes_chapters';

    protected $fillable = [
        'quizz_id',
        'chapter_id',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quizz_id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function quizQuestions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_chapter_id');
    }
}

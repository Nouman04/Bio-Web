<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Quiz extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'type',
    ];

    public function quizChapters(): HasMany
    {
        return $this->hasMany(QuizChapter::class, 'quizz_id');
    }

    /**
     * Chapters included in this quiz (via quizzes_chapters pivot).
     */
    public function chapters(): BelongsToMany
    {
        return $this->belongsToMany(Chapter::class, 'quizzes_chapters', 'quizz_id', 'chapter_id')
            ->withTimestamps();
    }

    public function userAttempts(): HasMany
    {
        return $this->hasMany(QuizUserAttempt::class);
    }
}

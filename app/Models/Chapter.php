<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'course_id',
        'title',
        'chapter_number',
        'description',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function questionBank(): HasMany
    {
        return $this->hasMany(QuestionBank::class);
    }

    public function videoLessons(): HasMany
    {
        return $this->hasMany(VideoLesson::class);
    }

    public function guides(): HasMany
    {
        return $this->hasMany(Guide::class);
    }

    public function diagrams(): HasMany
    {
        return $this->hasMany(Diagram::class);
    }

    public function quizChapters(): HasMany
    {
        return $this->hasMany(QuizChapter::class);
    }

    public function worksheetChapters(): HasMany
    {
        return $this->hasMany(WorksheetChapter::class);
    }
}

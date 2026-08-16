<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionableType extends Model
{
    protected $fillable = [
        'chapter_id',
        'question_id',
    ];

    public function questionable()
    {
        return $this->morphTo();
    }


    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function question()
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }
}

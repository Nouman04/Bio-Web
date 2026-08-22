<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionableType extends Model
{
    /**
     * The migration creates `questionable_type` (singular); without this
     * Eloquent would look for `questionable_types`.
     */
    protected $table = 'questionable_type';

    /**
     * The morph columns are fillable because the importer creates these rows
     * directly; elsewhere they are set by the `questionables()` relation.
     */
    protected $fillable = [
        'chapter_id',
        'question_id',
        'questionable_type',
        'questionable_id',
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

    /**
     * Where this question came from in a past paper, if it was cited.
     */
    public function pastPaper()
    {
        return $this->hasOne(PastPaperReference::class, 'questionable_type_id');
    }
}

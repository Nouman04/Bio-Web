<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionOption extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'question_bank_id',
        'title',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_bank_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuestionAnswer::class, 'question_option_id');
    }
}

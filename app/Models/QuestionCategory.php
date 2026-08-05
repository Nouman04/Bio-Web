<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(QuestionBank::class, 'question_categories_id');
    }
}

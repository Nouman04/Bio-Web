<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class QuestionCategory extends Model
{
    use SoftDeletes, HasUuid, Searchable;

    protected $fillable = [
        'type',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(QuestionBank::class, 'question_categories_id');
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
        ];
    }
}

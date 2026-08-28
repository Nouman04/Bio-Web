<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Status extends Model
{
    use HasUuid, Searchable;

    protected $fillable = [
        'status',
    ];

    public function statusable()
    {
        return $this->morphTo();
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
        ];
    }
}

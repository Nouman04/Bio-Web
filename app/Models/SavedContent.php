<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class SavedContent extends Model
{
    use HasUuid, Searchable;

    public function contentable()
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
            'contentable_type' => $this->contentable_type,
        ];
    }
}

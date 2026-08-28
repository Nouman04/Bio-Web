<?php

namespace App\Http\Resources;

use App\Models\Summary as Record;
use Illuminate\Http\Request;

/**
 * One summary in a chapter listing.
 *
 * The reader's progress is not part of this: it is per-user state the
 * controller loads in one query and keeps beside the collection, keyed by id.
 *
 * @mixin Record
 */
class SummaryResource extends AppResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Kept so the view can look this record up in the progress map.
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title ?: 'Untitled summary',
            'excerpt' => $this->excerpt ?: 'No content yet.',
            'topic' => $this->whenLoaded('topic', fn () => $this->topic?->title),
            'reading_minutes' => $this->reading_minutes,
            'updated_human' => $this->updated_at?->diffForHumans(),
        ];
    }
}

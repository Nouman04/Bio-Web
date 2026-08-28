<?php

namespace App\Http\Resources;

use App\Models\Guide as Record;
use Illuminate\Http\Request;

/**
 * One guide in a chapter listing.
 *
 * The reader's progress is not part of this: it is per-user state the
 * controller loads in one query and keeps beside the collection, keyed by id.
 *
 * @mixin Record
 */
class GuideResource extends AppResource
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
            'title' => $this->title ?: 'Untitled guide',
            'excerpt' => $this->excerpt ?: 'No content yet.',
            'type' => $this->type,
            'type_label' => $this->type_label,
            'topic' => $this->whenLoaded('topic', fn () => $this->topic?->title),
            'author' => $this->whenLoaded('addedBy', fn () => $this->addedBy?->name),
        ];
    }
}

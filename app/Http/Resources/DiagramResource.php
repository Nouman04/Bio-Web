<?php

namespace App\Http\Resources;

use App\Models\Diagram as Record;
use Illuminate\Http\Request;

/**
 * One diagram in a chapter gallery.
 *
 * The reader's progress is not part of this: it is per-user state the
 * controller loads in one query and keeps beside the collection, keyed by id.
 *
 * @mixin Record
 */
class DiagramResource extends AppResource
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
            'title' => $this->title ?: 'Untitled diagram',
            'excerpt' => $this->content ? \Illuminate\Support\Str::limit(strip_tags($this->content), 110) : 'No description yet.',
            'image_url' => $this->image_url,
            'topic' => $this->whenLoaded('topic', fn () => $this->topic?->title),
            'added_human' => $this->created_at?->diffForHumans(),
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Models\VideoLesson as Record;
use Illuminate\Http\Request;

/**
 * One video lesson in a chapter listing.
 *
 * The reader's progress is not part of this: it is per-user state the
 * controller loads in one query and keeps beside the collection, keyed by id.
 *
 * @mixin Record
 */
class VideoLessonResource extends AppResource
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
            'title' => $this->title,
            'excerpt' => $this->description ? \Illuminate\Support\Str::limit(strip_tags($this->description), 110) : 'No description yet.',
            'is_external' => $this->is_external,
            'video_url' => $this->video_url,
            'author' => $this->whenLoaded('addedBy', fn () => $this->addedBy?->name),
            'added_human' => $this->created_at?->diffForHumans(),
            // Tiles have no cover image, so the gradient is keyed off the id.
            'tint' => $this->id % 5,
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A topic as JSON — the picker, the parent search and the API all read it in
 * this shape, so it is described once.
 *
 * @mixin Topic
 */
class TopicResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'excerpt' => $this->excerpt,

            // Where it sits: the chapter tag makes two similarly named topics
            // tellable apart in a picker.
            'chapter' => $this->whenLoaded('chapter', fn () => [
                'uuid' => $this->chapter->uuid,
                'title' => $this->chapter->title,
                'course' => $this->chapter->course?->title,
            ]),

            'parent' => $this->whenLoaded('parent', fn () => $this->parent ? [
                'id' => $this->parent->id,
                'uuid' => $this->parent->uuid,
                'title' => $this->parent->title,
                'chapter' => $this->parent->chapter?->title,
            ] : null),

            'questions_count' => $this->whenCounted('questionables'),
            'attachments_count' => $this->whenCounted('attachments'),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

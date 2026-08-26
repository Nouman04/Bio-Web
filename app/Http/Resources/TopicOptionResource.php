<?php

namespace App\Http\Resources;

use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A topic as a type-ahead option: what Tom Select needs and nothing else.
 *
 * `meta` names the chapter and course, which is the whole point of the parent
 * picker — the same topic title often appears in several chapters.
 *
 * @mixin Topic
 */
class TopicOptionResource extends JsonResource
{
    /**
     * The picker posts ids, so this deliberately keeps its own envelope off.
     */
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'text' => $this->title,
            'meta' => implode(' • ', array_filter([
                $this->chapter?->title,
                $this->chapter?->course?->title,
            ])),
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Models\Course;
use Illuminate\Http\Request;

/**
 * A course as a card: the catalog, the public gallery and the student hub all
 * show the same handful of facts, so they are described once here.
 *
 * Progress is not part of this — it is per-user state the controller works out
 * for the whole page in one query, keyed by id.
 *
 * @mixin Course
 */
class CourseCardResource extends AppResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Kept so the view can look this course up in the progress map.
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'excerpt' => $this->excerpt ?: 'No description yet.',
            'category' => $this->whenLoaded('category', fn () => $this->category?->title),
            'instructor' => $this->whenLoaded('creator', fn () => $this->creator?->name),
            'chapters_count' => (int) ($this->chapters_count ?? 0),
            'on_sale' => $this->hasStripePlan(),
            'price' => $this->whenLoaded('plan', fn () => $this->plan?->formatted_price),
            // Courses carry no cover image, so the gradient is keyed off the id.
            'tint' => $this->id % 5,
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A course as JSON.
 *
 * @mixin Course
 */
class CourseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'category' => $this->whenLoaded('category', fn () => $this->category?->title),
            'instructor' => $this->whenLoaded('creator', fn () => $this->creator?->name),
            'chapters_count' => $this->whenCounted('chapters'),
            'plan' => $this->whenLoaded('plan', fn () => $this->plan ? [
                'price' => $this->plan->formatted_price,
                'interval' => $this->plan->billing_interval,
                'on_sale' => $this->plan->isSellable(),
            ] : null),
        ];
    }
}

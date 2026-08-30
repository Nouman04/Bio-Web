<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\CoursePrice;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\ApiErrorException;

/**
 * Everything that happens to a course, and to the chapter visibility settings
 * that hang off it.
 */
class CourseService
{
    public function __construct(
        private readonly AttachmentService $attachments,
        private readonly StripeService $stripe,
    ) {
    }

    /**
     * The courses list, filtered as the filter card asks.
     *
     * @param  array<string, mixed>  $filters
     */
    public function listing(array $filters = []): Builder
    {
        return Course::query()
            ->with(['category:id,title', 'creator:id,name', 'image'])
            ->withCount('chapters')
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query
                ->where(fn (Builder $q) => $q
                    ->where('title', 'like', "%{$search}%")
                    ->orWhereHas('creator', fn ($c) => $c->where('name', 'like', "%{$search}%"))))
            ->when($filters['category'] ?? null, fn (Builder $query, string $uuid) => $query
                ->whereRelation('category', 'uuid', $uuid))
            ->when($filters['created_by'] ?? null, fn (Builder $query, string $uuid) => $query
                ->whereRelation('creator', 'uuid', $uuid));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $author, ?UploadedFile $image = null): Course
    {
        $data['created_by'] = $author->id;

        $course = Course::create($this->withoutImage($data));

        $this->attachments->replace($course, $image, 'courses', Attachment::IMAGE);

        return $course->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Course $course, array $data, ?UploadedFile $image = null): Course
    {
        $course->update($this->withoutImage($data));

        // No new upload leaves the existing cover alone, so editing the title
        // does not silently drop the image.
        $this->attachments->replace($course, $image, 'courses', Attachment::IMAGE);

        return $course->fresh();
    }

    /**
     * The cover arrives in the same validated payload as the rest, but it is
     * not a column on the course.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withoutImage(array $data): array
    {
        unset($data['image']);

        return $data;
    }

    public function delete(Course $course): void
    {
        $course->delete();
    }

    /**
     * Puts a course on sale at a new price.
     *
     * The old price is left where it is: a new row is written and the newest
     * one for the interval is what is charged from now on, so what the course
     * used to cost stays answerable. Stripe is told about the change where a
     * key is configured — a Stripe price is immutable, so that means a new
     * price object and the old one deactivated, which is what syncCoursePlan
     * already does.
     *
     * @param  array{price:int, billing_interval:string, promo_code?:string|null, promo_type?:string|null, promo_value?:int|null, promo_expires_at?:string|null}  $terms
     */
    public function reprice(Course $course, array $terms, User $author): CoursePrice
    {
        $price = DB::transaction(function () use ($course, $terms, $author) {
            return $course->prices()->create([
                'billing_interval' => $terms['billing_interval'],
                'price' => $terms['price'],
                'currency' => StripeService::CURRENCY,
                'promo_code' => $terms['promo_code'] ?? null,
                'promo_type' => ($terms['promo_code'] ?? null) ? ($terms['promo_type'] ?? 'percent') : null,
                'promo_value' => ($terms['promo_code'] ?? null) ? ($terms['promo_value'] ?? null) : null,
                'promo_expires_at' => ($terms['promo_code'] ?? null) ? ($terms['promo_expires_at'] ?? null) : null,
                'created_by' => $author->id,
            ]);
        });

        // course_plans stays the pointer at what is sold today, so the
        // public pages and the checkout follow the new price whether or not a
        // Stripe key is configured.
        $plan = $this->stripe->planFor($course, $price->billing_interval);
        $plan->forceFill(['price' => $price->price, 'currency' => $price->currency])->save();

        $this->sellAt($course, $price);

        $course->unsetRelation('prices');

        return $price->fresh();
    }

    /**
     * Tells Stripe about a new price, and records which price object it became.
     *
     * Without a key configured there is nothing to tell, and the row still
     * stands as the local record — the same way the seeder and the pricing
     * command already behave offline.
     */
    private function sellAt(Course $course, CoursePrice $price): void
    {
        if (! $this->stripe->configured()) {
            return;
        }

        try {
            $plan = $this->stripe->syncCoursePlan($course, [
                'price' => $price->price,
                'billing_interval' => $price->billing_interval,
                'description' => strip_tags((string) $course->description),
            ]);

            $price->forceFill(['stripe_price_id' => $plan->stripe_price_id])->save();

            $this->stripe->syncPromotionCode($course, $price);
        } catch (ApiErrorException $e) {
            // The price is recorded either way; what Stripe would not accept is
            // reported to whoever set it rather than swallowed.
            throw $e;
        }
    }

    /**
     * Saves the visibility chosen for each chapter, and says how many actually
     * changed.
     *
     * Only chapters belonging to this course are written, so a forged id cannot
     * reach another course's.
     *
     * @param  array<int|string, string>  $visibilities
     */
    public function saveChapterVisibility(Course $course, array $visibilities): int
    {
        $owned = $course->chapters()->pluck('id')->all();
        $changed = 0;

        foreach ($visibilities as $id => $visibility) {
            if (! in_array((int) $id, $owned, true)) {
                continue;
            }

            $changed += Chapter::where('id', $id)
                ->where('visibility', '!=', $visibility)
                ->update(['visibility' => $visibility]);
        }

        return $changed;
    }

    /**
     * The chapters shown on the configuration page.
     */
    public function chaptersFor(Course $course)
    {
        return $course->chapters()->orderBy('chapter_number')->get();
    }
}

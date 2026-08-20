<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CoursePlan;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Checkout;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\Product;
use Stripe\StripeClient;

/**
 * Every call to Stripe goes through here — the seeder, the controllers and
 * anything added later. Nothing else should reach for the Stripe SDK or
 * Cashier's client directly, so the account is only ever touched in one place.
 */
class StripeService
{
    /**
     * The currency plans are created in.
     */
    public const CURRENCY = 'usd';

    /**
     * Intervals Stripe recurring prices may use here.
     */
    public const INTERVALS = ['month', 'year'];

    /**
     * Whether a secret key is configured. Without one every write is skipped
     * rather than throwing, so seeding and browsing still work offline.
     */
    public function configured(): bool
    {
        return filled(config('cashier.secret'));
    }

    /**
     * The underlying client, for the rare call this service does not wrap.
     */
    public function client(): StripeClient
    {
        return Cashier::stripe();
    }

    /* ── Plans ──────────────────────────────────────────────────────────── */

    /**
     * The course's plan row, created empty the first time it is asked for.
     */
    public function planFor(Course $course): CoursePlan
    {
        return $course->plan()->firstOrCreate([], ['currency' => self::CURRENCY]);
    }

    /**
     * Gives a course a Stripe product and recurring price, reusing whatever its
     * plan already points at. Safe to call repeatedly: it only writes to Stripe
     * when something is missing or the terms have changed.
     *
     * @param  array{price:int, billing_interval:string, description?:string}  $terms
     *
     * @throws ApiErrorException
     */
    public function syncCoursePlan(Course $course, array $terms): CoursePlan
    {
        $plan = $this->planFor($course);

        $product = $this->productFor($course, $plan, $terms);
        $plan->stripe_product_id = $product->id;

        $price = $this->priceFor($course, $plan, $product, $terms);
        $plan->stripe_price_id = $price->id;

        $plan->price = $terms['price'];
        $plan->currency = self::CURRENCY;
        $plan->billing_interval = $terms['billing_interval'];
        $plan->save();

        // So a caller reading $course->plan straight after sees the new row.
        $course->setRelation('plan', $plan);

        return $plan;
    }

    /**
     * The plan's product, created if it has none or the recorded one is gone.
     */
    private function productFor(Course $course, CoursePlan $plan, array $terms): Product
    {
        if ($plan->stripe_product_id && $product = $this->findProduct($plan->stripe_product_id)) {
            return $product;
        }

        return $this->client()->products->create([
            'name' => $course->title,
            'description' => Str::limit(strip_tags($terms['description'] ?? $course->description ?? ''), 350) ?: null,
            'metadata' => [
                'course_uuid' => $course->uuid,
                'course_slug' => $course->slug,
            ],
        ]);
    }

    /**
     * The plan's price. A Stripe price is immutable, so changed terms mean a
     * new price and the old one is deactivated rather than deleted.
     */
    private function priceFor(Course $course, CoursePlan $plan, Product $product, array $terms): Price
    {
        $price = $plan->stripe_price_id ? $this->findPrice($plan->stripe_price_id) : null;

        if ($price && $this->priceMatches($price, $terms)) {
            return $price;
        }

        if ($price && $price->active) {
            $this->client()->prices->update($price->id, ['active' => false]);
        }

        return $this->client()->prices->create([
            'product' => $product->id,
            'unit_amount' => $terms['price'],
            'currency' => self::CURRENCY,
            'recurring' => ['interval' => $terms['billing_interval']],
            'metadata' => ['course_uuid' => $course->uuid],
        ]);
    }

    private function priceMatches(Price $price, array $terms): bool
    {
        return $price->active
            && $price->unit_amount === $terms['price']
            && $price->currency === self::CURRENCY
            && ($price->recurring->interval ?? null) === $terms['billing_interval'];
    }

    /**
     * A product that may have been deleted in Stripe since it was recorded.
     */
    public function findProduct(string $id): ?Product
    {
        try {
            $product = $this->client()->products->retrieve($id);

            return $product->active ? $product : null;
        } catch (ApiErrorException) {
            return null;
        }
    }

    public function findPrice(string $id): ?Price
    {
        try {
            return $this->client()->prices->retrieve($id);
        } catch (ApiErrorException) {
            return null;
        }
    }

    /* ── Checkout ───────────────────────────────────────────────────────── */

    /**
     * Starts a Stripe Checkout session subscribing the user to a course, and
     * returns something the controller can redirect to.
     *
     * @throws ApiErrorException
     */
    public function checkoutForCourse(User $user, Course $course, string $successUrl, string $cancelUrl): Checkout
    {
        return $user
            ->newSubscription($this->subscriptionName($course), $course->plan?->stripe_price_id)
            ->checkout([
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'course_uuid' => $course->uuid,
                    'course_title' => $course->title,
                ],
            ]);
    }

    /**
     * Cashier keys a subscription by name, so each course gets its own.
     */
    public function subscriptionName(Course $course): string
    {
        return 'course_' . $course->uuid;
    }

    /**
     * Whether this user already subscribes to this course.
     */
    public function subscribedTo(?User $user, Course $course): bool
    {
        return (bool) $user?->subscribed($this->subscriptionName($course));
    }
}

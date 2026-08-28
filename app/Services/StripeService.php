<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CoursePlan;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Checkout;
use Laravel\Cashier\Subscription;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\Product;
use Stripe\StripeClient;
use Stripe\Stripe;
use Exception;

/**
 * Every call to Stripe goes through here — the seeder, the controllers and
 * anything added later. Nothing else should reach for the Stripe SDK or
 * Cashier's client directly, so the account is only ever touched in one place.
 */
class StripeService
{

    public function __construct()
    {
        Stripe::setApiKey(config('cashier.secret') ?? config('services.stripe.secret'));
    }

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
     * The course's plan on one set of terms, created empty the first time it is
     * asked for. A course has one plan per interval.
     */
    public function planFor(Course $course, string $interval = 'month'): CoursePlan
    {
        return $course->plans()->firstOrCreate(
            ['billing_interval' => $interval],
            ['currency' => self::CURRENCY]
        );
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
        $plan = $this->planFor($course, $terms['billing_interval']);

        $product = $this->productFor($course, $plan, $terms);
        $plan->stripe_product_id = $product->id;

        $price = $this->priceFor($course, $plan, $product, $terms);
        $plan->stripe_price_id = $price->id;

        $plan->price = $terms['price'];
        $plan->currency = self::CURRENCY;
        $plan->billing_interval = $terms['billing_interval'];
        $plan->save();

        // A caller reading $course->plans straight after should see it.
        $course->unsetRelation('plans')->unsetRelation('plan');

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
    public function checkoutForCourse(User $user, Course $course, string $successUrl, string $cancelUrl, string $interval = 'month'): Checkout
    {
        $plan = $course->planFor($interval) ?? $course->plan;

        return $user
            ->newSubscription($this->subscriptionName($course), $plan?->stripe_price_id)
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
     * Records a finished Checkout session as a local subscription.
     *
     * Stripe's webhook does this too, but a webhook cannot reach a machine that
     * is not on the internet — so the reader coming back from Stripe is enough
     * on its own. Both paths key off the same Stripe subscription id, so
     * whichever arrives first wins and the other is a no-op.
     *
     * Returns null when the session is not a paid subscription, so a tampered
     * or abandoned session grants nothing.
     *
     * @throws ApiErrorException
     */
    public function recordCheckout(User $user, Course $course, string $sessionId): ?Subscription
    {
        $session = $this->client()->checkout->sessions->retrieve($sessionId, [
            'expand' => ['subscription'],
        ]);

        // The session must belong to this customer, or anyone holding a session
        // id could subscribe someone else's account.
        if (! $session->subscription || $session->customer !== $user->stripe_id) {
            return null;
        }

        if (! in_array($session->status, ['complete'], true)) {
            return null;
        }

        $stripeSubscription = $session->subscription;
        $item = $stripeSubscription->items->data[0] ?? null;

        $subscription = $user->subscriptions()->updateOrCreate(
            ['stripe_id' => $stripeSubscription->id],
            [
                'type' => $this->subscriptionName($course),
                'stripe_status' => $stripeSubscription->status,
                'stripe_price' => $item?->price?->id,
                'quantity' => $item?->quantity,
                'ends_at' => null,
            ]
        );

        // Cashier reads the price off the items table for multi-price plans.
        if ($item) {
            $subscription->items()->updateOrCreate(
                ['stripe_id' => $item->id],
                [
                    'stripe_product' => $item->price->product,
                    'stripe_price' => $item->price->id,
                    'quantity' => $item->quantity,
                ]
            );
        }

        return $subscription;
    }

    /**
     * Cashier keys a subscription by name, so each course gets its own.
     */
    public function subscriptionName(Course $course): string
    {
        return 'course_' . $course->uuid;
    }

    /**
     * The uuids of every course this user currently subscribes to.
     *
     * Cashier keys each subscription as `course_{uuid}`, so the uuid is read
     * back off the type. Only valid subscriptions count — an active one, or a
     * cancelled one still inside its paid period.
     *
     * @return array<int, string>
     */
    public function subscribedCourseUuids(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return $user->subscriptions
            ->filter(fn (Subscription $subscription) => $subscription->valid())
            ->map(fn (Subscription $subscription) => Str::after($subscription->type, 'course_'))
            ->filter(fn (string $uuid) => $uuid !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Whether this user already subscribes to this course.
     */
    public function subscribedTo(?User $user, Course $course): bool
    {
        return (bool) $user?->subscribed($this->subscriptionName($course));
    }

    public function clearAllPlans(): array
    {
        $pricesResult = $this->clearPrices();
        $productsResult = $this->clearProducts();

        return [
            'deleted' => array_merge($pricesResult['deleted'] ?? [], $productsResult['deleted'] ?? []),
            'archived' => array_merge($pricesResult['archived'] ?? [], $productsResult['archived'] ?? []),
        ];
    }

    public function clearPrices(): array
    {
        $archived = [];
        $prices = Price::all(['limit' => 100]);

        foreach ($prices->autoPagingIterator() as $price) {
            Price::update($price->id, ['active' => false]);
            $archived[] = "Price: {$price->id}";
        }

        return ['archived' => $archived];
    }

    public function clearProducts(): array
    {
        $deleted = [];
        $archived = [];

        $products = Product::all(['limit' => 100]);

        foreach ($products->autoPagingIterator() as $product) {
            try {
                $product->delete();
                $deleted[] = "Product: {$product->id}";
            } catch (Exception $e) {
                Product::update($product->id, ['active' => false]);
                $archived[] = "Product: {$product->id} (has active history)";
            }
        }

        return compact('deleted', 'archived');
    }


}

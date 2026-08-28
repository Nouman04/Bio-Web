<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CoursePlan;
use App\Services\StripeService;
use Illuminate\Database\Seeder;
use Stripe\Exception\ApiErrorException;

/**
 * Puts every course in the catalogue on sale, on two sets of terms.
 *
 *     php artisan db:seed --class=StripeSubscriptionSeeder
 *
 * Every Stripe call goes through StripeService, so this seeder only decides
 * what to charge. Needs STRIPE_SECRET in .env; with a test key it only ever
 * touches test mode. Without one the prices are still recorded locally, just
 * without a Stripe product behind them.
 *
 * Safe to run again: an unchanged price is left alone, and a changed one gets a
 * new Stripe price with the old one deactivated.
 */
class StripeSubscriptionSeeder extends Seeder
{
    /**
     * The monthly price is picked from this range, in whole dollars.
     */
    private const MONTHLY_MIN = 11;

    private const MONTHLY_MAX = 20;

    /**
     * What paying for a year up front saves against paying monthly.
     */
    private const YEARLY_DISCOUNT = 0.20;

    public function __construct(private readonly StripeService $stripe)
    {
    }

    public function run(): void
    {
        if (! $this->stripe->configured()) {
            $this->command?->warn('STRIPE_SECRET is not set — prices will be recorded without a Stripe plan.');
        }

        $courses = Course::orderBy('id')->get();

        if ($courses->isEmpty()) {
            $this->command?->warn('No courses to price — run the course seeders first.');

            return;
        }

        foreach ($courses as $course) {
            // In the smallest currency unit, the way Stripe counts.
            $monthly = random_int(self::MONTHLY_MIN, self::MONTHLY_MAX) * 100;
            $yearly = $this->yearlyFor($monthly);

            $this->command?->info("{$course->title}");

            foreach (['month' => $monthly, 'year' => $yearly] as $interval => $price) {
                $this->price($course, $interval, $price);
            }

            $this->command?->line(sprintf(
                '    saves %s a year against paying monthly',
                $this->money($monthly * 12 - $yearly)
            ));
        }
    }

    /**
     * A year up front costs twelve months less the discount, rounded to whole
     * dollars so the price reads like a price.
     */
    private function yearlyFor(int $monthly): int
    {
        $full = $monthly * 12;

        return (int) (round($full * (1 - self::YEARLY_DISCOUNT) / 100) * 100);
    }

    /**
     * Records one set of terms locally, and mirrors it into Stripe when there
     * is an account to mirror it into.
     */
    private function price(Course $course, string $interval, int $amount): void
    {
        // Recorded either way; only the Stripe ids need an account to reach.
        $plan = $this->stripe->planFor($course, $interval);

        $plan->fill([
            'price' => $amount,
            'currency' => StripeService::CURRENCY,
            'billing_interval' => $interval,
        ])->save();

        if (! $this->stripe->configured()) {
            $this->report($plan, null);

            return;
        }

        try {
            $plan = $this->stripe->syncCoursePlan($course, [
                'price' => $amount,
                'billing_interval' => $interval,
                'description' => strip_tags((string) $course->description),
            ]);

            $this->report($plan, $plan->stripe_price_id);
        } catch (ApiErrorException $e) {
            // One rejected price should not abandon the rest of the run.
            $this->command?->error("    {$interval}: Stripe rejected this — {$e->getMessage()}");
        }
    }

    private function report(CoursePlan $plan, ?string $priceId): void
    {
        $this->command?->line(sprintf(
            '    %-5s %s per %s%s',
            $plan->billing_interval,
            $plan->formatted_price,
            $plan->billing_interval,
            $priceId ? "  [{$priceId}]" : '  (not on Stripe)'
        ));
    }

    private function money(int $amount): string
    {
        return '$' . number_format($amount / 100, 2);
    }
}

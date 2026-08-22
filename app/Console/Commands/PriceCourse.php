<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Services\StripeService;
use Illuminate\Console\Command;
use Stripe\Exception\ApiErrorException;

/**
 * Puts one course on sale: gives it a plan row and the matching Stripe product
 * and recurring price. Until a course has been through this it shows as "Not on
 * sale yet" and has no Subscribe button.
 *
 *     php artisan course:price {uuid|slug} --amount=19.00 --interval=month
 *
 * Safe to run again — StripeService reuses the product and only makes a new
 * price when the terms have actually changed.
 */
class PriceCourse extends Command
{
    protected $signature = 'course:price
        {course : The course uuid or slug}
        {--amount= : The price a customer pays, e.g. 19.00}
        {--interval=month : month or year}';

    protected $description = 'Put a course on sale through Stripe';

    public function handle(StripeService $stripe): int
    {
        if (! $stripe->configured()) {
            $this->error('STRIPE_SECRET is not set — nothing to sell through.');

            return self::FAILURE;
        }

        $course = $this->course();

        if (! $course) {
            $this->error("No course matches \"{$this->argument('course')}\".");

            return self::FAILURE;
        }

        $interval = (string) $this->option('interval');

        if (! in_array($interval, StripeService::INTERVALS, true)) {
            $this->error('--interval must be one of: ' . implode(', ', StripeService::INTERVALS));

            return self::FAILURE;
        }

        $amount = $this->option('amount') ?? $this->ask('Price per ' . $interval . ' (e.g. 19.00)');

        if (! is_numeric($amount) || (float) $amount <= 0) {
            $this->error('--amount must be a positive number, e.g. 19.00');

            return self::FAILURE;
        }

        try {
            // Stripe counts in the smallest currency unit: 19.00 is 1900.
            $plan = $stripe->syncCoursePlan($course, [
                'price' => (int) round((float) $amount * 100),
                'billing_interval' => $interval,
                'description' => strip_tags((string) $course->description),
            ]);
        } catch (ApiErrorException $e) {
            $this->error("Stripe rejected this — {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info("{$course->title} is on sale at {$plan->formatted_price} per {$plan->billing_interval}.");
        $this->line("  product: {$plan->stripe_product_id}");
        $this->line("  price:   {$plan->stripe_price_id}");
        $this->line('  page:    ' . route('public.subscribe.plans', $course));

        return self::SUCCESS;
    }

    private function course(): ?Course
    {
        $key = (string) $this->argument('course');

        return Course::where('uuid', $key)->orWhere('slug', $key)->first();
    }
}

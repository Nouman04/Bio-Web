<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * What a course has cost, over time.
 *
 * Repricing a course used to overwrite `course_plans.price`, which lost what it
 * had been before — so there was no way to answer "what were people paying in
 * March?". Each change writes a new row here instead, and the newest row for an
 * interval is the price in force. `course_plans` stays as the pointer at what
 * Stripe currently sells; this is the record of how it got there.
 *
 * A promo code belongs to the price it discounts, so it is carried on the row
 * rather than kept apart: changing the price and changing the offer are the
 * same act.
 *
 * `price` and `promo_value` are in the smallest currency unit, the way Stripe
 * counts — 1900 is $19.00.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_prices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->enum('billing_interval', ['month', 'year'])->default('month');
            $table->unsignedInteger('price');
            $table->char('currency', 3)->default('usd');

            // The offer running against this price, if there is one.
            $table->string('promo_code')->nullable();
            $table->enum('promo_type', ['percent', 'amount'])->nullable();
            $table->unsignedInteger('promo_value')->nullable();
            $table->timestamp('promo_expires_at')->nullable();

            // What Stripe was selling at this price, so an old row can still be
            // traced back to the price object customers were charged against.
            $table->string('stripe_price_id')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // "The newest price for this course on these terms" is the only
            // read this table has, so it is the index it gets.
            $table->index(['course_id', 'billing_interval', 'id']);
        });

        // Whatever each course is on sale at now becomes the first entry in its
        // history, so a course that has never been repriced still has one.
        $now = now();

        foreach (DB::table('course_plans')->whereNotNull('price')->whereNull('deleted_at')->get() as $plan) {
            DB::table('course_prices')->insert([
                'uuid' => (string) Str::uuid(),
                'course_id' => $plan->course_id,
                'billing_interval' => $plan->billing_interval,
                'price' => $plan->price,
                'currency' => $plan->currency,
                'stripe_price_id' => $plan->stripe_price_id,
                'created_at' => $plan->created_at ?? $now,
                'updated_at' => $plan->updated_at ?? $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('course_prices');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * What a course is sold for, and what it maps to in Stripe. Kept apart from
     * the course itself so a course carries no billing columns: one row per
     * course, removed with it.
     *
     * `price` is in the smallest currency unit, the way Stripe counts — 1900
     * is $19.00.
     */
    public function up(): void
    {
        Schema::create('course_plans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('course_id')->unique()->constrained('courses')->cascadeOnDelete();
            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_price_id')->nullable();
            $table->unsignedInteger('price')->nullable();
            $table->char('currency', 3)->default('usd');
            $table->enum('billing_interval', ['month', 'year'])->default('month');
            $table->timestamps();
            $table->softDeletes();

            $table->index('stripe_price_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_plans');
    }
};

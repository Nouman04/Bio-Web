<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\CoursePlan;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;

/**
 * Seeds the courses that are sold as subscriptions, and gives each one a Stripe
 * product with a recurring price. Every Stripe call goes through StripeService,
 * so this seeder only decides what to sell and on what terms.
 *
 *     php artisan db:seed --class=StripeSubscriptionSeeder
 *
 * Needs STRIPE_SECRET in .env. With a test key it only ever touches test mode;
 * without one the courses are still seeded, just without a plan.
 */
class StripeSubscriptionSeeder extends Seeder
{
    public function __construct(private readonly StripeService $stripe)
    {
    }

    /**
     * The courses to sell, and the terms each is sold on. `price` is in the
     * smallest currency unit, the way Stripe counts — 1900 is $19.00.
     */
    private const PLANS = [
        [
            'title' => 'IGCSE Biology — Complete Syllabus',
            'description' => 'Every chapter of the IGCSE Biology syllabus, with notes, flashcards, quizzes and the full question bank.',
            'price' => 1900,
            'billing_interval' => 'month',
        ],
        [
            'title' => 'IGCSE Chemistry — Complete Syllabus',
            'description' => 'Structured chemistry chapters covering atomic structure through organic reactions, with practice at every step.',
            'price' => 1900,
            'billing_interval' => 'month',
        ],
        [
            'title' => 'IGCSE Physics — Complete Syllabus',
            'description' => 'Mechanics, waves, electricity and nuclear physics, taught through worked examples and interactive practice.',
            'price' => 1900,
            'billing_interval' => 'month',
        ],
        [
            'title' => 'Biology Exam Intensive',
            'description' => 'A revision-season course: past-paper drills, mark-scheme walkthroughs and timed theory practice.',
            'price' => 2900,
            'billing_interval' => 'month',
        ],
        [
            'title' => 'All Access — Annual',
            'description' => 'Every course in the library on one annual subscription, including new material as it is published.',
            'price' => 19000,
            'billing_interval' => 'year',
        ],
    ];

    public function run(): void
    {
        if (! $this->stripe->configured()) {
            $this->command?->warn('STRIPE_SECRET is not set — courses will be seeded without a Stripe plan.');
        }

        $author = $this->author();
        $category = $this->category();

        foreach (self::PLANS as $plan) {
            $course = $this->course($plan, $author, $category);

            // The terms are recorded locally either way; only the Stripe ids
            // need an account to reach.
            $this->terms($course, $plan);

            if (! $this->stripe->configured()) {
                continue;
            }

            try {
                $sold = $this->stripe->syncCoursePlan($course, $plan);

                $this->command?->line(
                    "  plan: {$sold->stripe_product_id} / {$sold->stripe_price_id}"
                    . " — {$sold->formatted_price} per {$sold->billing_interval}"
                );
            } catch (ApiErrorException $e) {
                // One bad plan should not abandon the rest of the run.
                $this->command?->error("  {$course->title}: Stripe rejected this — {$e->getMessage()}");
            }
        }
    }

    /**
     * Creates the course locally, or picks up the one already there.
     */
    private function course(array $plan, User $author, Category $category): Course
    {
        $course = Course::firstOrNew(['slug' => Str::slug($plan['title'])]);

        $course->fill([
            'title' => $plan['title'],
            'description' => '<p>' . e($plan['description']) . '</p>',
        ]);

        // Only set on creation, so a seeded course keeps its real owner.
        $course->created_by ??= $author->id;
        $course->category_id ??= $category->id;

        $course->save();

        $this->command?->info(($course->wasRecentlyCreated ? 'Created' : 'Updated') . " course: {$course->title}");

        return $course;
    }

    /**
     * Writes what the course is sold for onto its plan row. Stripe is not
     * involved — that is syncCoursePlan's job.
     */
    private function terms(Course $course, array $plan): CoursePlan
    {
        $sold = $this->stripe->planFor($course);

        $sold->fill([
            'price' => $plan['price'],
            'currency' => StripeService::CURRENCY,
            'billing_interval' => $plan['billing_interval'],
        ])->save();

        $course->setRelation('plan', $sold);

        return $sold;
    }

    /**
     * Seeded courses are credited to an admin, falling back to any user.
     */
    private function author(): User
    {
        $author = User::whereHas('roles', fn ($query) => $query->whereIn('name', ['admin', 'Admin']))->first()
            ?? User::first();

        if (! $author) {
            throw new \RuntimeException('No users exist — run UserSeeder before this seeder.');
        }

        return $author;
    }

    private function category(): Category
    {
        return Category::firstOrCreate(
            ['title' => 'Subscriptions'],
            ['slug' => 'subscriptions']
        );
    }
}

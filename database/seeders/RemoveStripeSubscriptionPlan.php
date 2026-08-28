<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Services\StripeService;
use Illuminate\Database\Seeder;


class RemoveStripeSubscriptionPlan extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(StripeService $stripeService): void
    {
        $this->command->info('Clearing Stripe plans...');

        $results = $stripeService->clearAllPlans();

        foreach ($results['deleted'] as $item) {
            $this->command->info("Deleted {$item}");
        }

        foreach ($results['archived'] as $item) {
            $this->command->warn("Archived {$item}");
        }

        $this->command->info('Stripe cleanup completed.');
    }
}

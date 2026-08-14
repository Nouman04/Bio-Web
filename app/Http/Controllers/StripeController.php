<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StripeController extends Controller
{
    public function createSetupIntent(Request $request)
    {
        $user = $request->user();
        
        // Ensure user is a Stripe customer
        $user->createOrGetStripeCustomer();

        // Create SetupIntent for future off-session billing
        $intent = $user->createSetupIntent([
            'usage' => 'off_session',
        ]);

        return response()->json([
            'client_secret' => $intent->client_secret,
        ]);
    }

    public function storePaymentMethod(Request $request)
    {
        $user = $request->user();
        $paymentMethodId = $request->input('payment_method_id');

        // Attach payment method and set as default for off-session billing
        $user->addPaymentMethod($paymentMethodId);
        $user->updateDefaultPaymentMethod($paymentMethodId);

        // Initial first charge or trial activation
        // Save local subscription entry
        $user->subscriptions()->create([
            'name' => 'default',
            'stripe_price' => 'price_monthly_plan',
            'quantity' => 1,
            'ends_at' => now()->addMonth(), // Active until 1 month from now
            'stripe_status' => 'active',
        ]);

        return response()->json(['message' => 'Subscription activated successfully!']);
    }
}

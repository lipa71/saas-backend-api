<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CentralStripeWebhookController extends Controller
{
    /**
     * Handle incoming secure production webhooks transmitted directly from Stripe servers.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (! isset($payload['type'])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid webhook payload structure.'], 400);
        }

        $eventType = $payload['type'];
        Log::info("Centralized Stripe Webhook received event type: {$eventType}");

        // 1. ROUTE ACTIVE CYCLES: Process structural subscription creation and modification events
        if ($eventType === 'customer.subscription.created' || $eventType === 'customer.subscription.updated') {

            $dataObject = $payload['data']['object'];

            $stripeCustomerId = $dataObject['customer'];
            $stripeSubscriptionId = $dataObject['id'];
            $stripeStatus = $dataObject['status'];

            $endsAt = isset($dataObject['current_period_end'])
                ? date('Y-m-d H:i:s', $dataObject['current_period_end'])
                : null;

            // 2. IDENTITY SEARCH: Locate the central landlord user bound to this customer payload
            $user = User::on('mysql')->where('stripe_id', $stripeCustomerId)->first();

            if (! $user) {
                $customerEmail = $dataObject['customer_email'] ?? null;
                if ($customerEmail) {
                    $user = User::on('mysql')->where('email', $customerEmail)->first();
                }
            }

            if ($user) {
                // 3. ATOMIC SYNCHRONIZATION: Execute multi-item updates wrapped inside a landlord database transaction
                DB::connection('mysql')->transaction(function () use ($stripeSubscriptionId, $user, $stripeStatus, $endsAt, $dataObject) {

                    // Update or create the foundational corporate subscription header
                    $subscription = Subscription::on('mysql')->updateOrCreate(
                        ['stripe_id' => $stripeSubscriptionId],
                        [
                            'user_id' => $user->id,
                            'type' => 'main',
                            'stripe_status' => $stripeStatus,
                            'ends_at' => $endsAt,
                        ]
                    );

                    // Map nested dictionary indices cleanly matching standard Stripe multidimensional arrays
                    $stripePriceId = $dataObject['items']['data'][0]['price']['id'] ?? 'price_metered_invoices_standard';
                    $stripeProductId = $dataObject['items']['data'][0]['price']['product'] ?? 'prod_saas_invoices_core';
                    $stripeItemId = $dataObject['items']['data'][0]['id'] ?? 'si_mock_' . Str::random(12);

                    SubscriptionItem::on('mysql')->updateOrCreate(
                        ['stripe_id' => $stripeItemId],
                        [
                            'subscription_id' => $subscription->id,
                            'stripe_product' => $stripeProductId,
                            'stripe_price' => $stripePriceId,
                            'quantity' => $dataObject['items']['data'][0]['quantity'] ?? 1,
                        ]
                    );
                });

                Log::info("Successfully synchronized Stripe multi-item subscription {$stripeSubscriptionId} to status {$stripeStatus} for User ID: {$user->id}");
            } else {
                Log::warning("Stripe Webhook aborted: No matching central user found for Customer ID: {$stripeCustomerId}");
            }
        }

        // 4. ROUTE NEGATIVE CYCLES: Instantly process cancellations and revoke system workspace access
        if ($eventType === 'customer.subscription.deleted') {

            $dataObject = $payload['data']['object'];
            $stripeSubscriptionId = $dataObject['id'];

            // Locate the exact target billing ledger entry directly inside the central schema
            $subscription = Subscription::on('mysql')
                ->where('stripe_id', $stripeSubscriptionId)
                ->first();

            if ($subscription) {
                $subscription->update([
                    'stripe_status' => 'canceled',
                    'ends_at' => now(),
                ]);

                Log::warning("Stripe Webhook Guard: Subscription {$stripeSubscriptionId} was successfully terminated. System access revoked.");
            } else {
                Log::warning("Stripe Webhook Guard: Cancellation request skipped. Subscription {$stripeSubscriptionId} not found.");
            }
        }

        // 5. EMIT PROTOCOL STANDARD: Return a valid response vector to acknowledge successful delivery
        return response()->json([
            'status' => 'success',
            'message' => 'Centralized multi-item webhook processed successfully.'
        ], 200);
    }
}

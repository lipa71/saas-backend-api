<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CentralStripeWebhookController extends Controller
{
    /**
     * Handle incoming secure production webhooks transmitted directly from Stripe servers.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        // 1. Parse the raw JSON payroll payload directly from the request stream
        $payload = $request->all();

        if (! isset($payload['type'])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid webhook payload structure.'], 400);
        }

        $eventType = $payload['type'];
        Log::info("Centralized Stripe Webhook received event type: {$eventType}");

        // 2. Route events matching the core subscription life cycles
        if ($eventType === 'customer.subscription.created' || $eventType === 'customer.subscription.updated') {

            $dataObject = $payload['data']['object'];

            $stripeCustomerId = $dataObject['customer']; // e.g., 'cus_R4729472'
            $stripeSubscriptionId = $dataObject['id'];   // e.g., 'sub_123456789'
            $stripeStatus = $dataObject['status'];       // active, past_due, trialing, canceled

            // Format timestamps correctly for regional carbon transformations
            $endsAt = isset($dataObject['current_period_end'])
                ? date('Y-m-d H:i:s', $dataObject['current_period_end'])
                : null;

            // 3. Locate the central master user owner account bound to this Stripe payment profile
            $user = User::on('mysql')->where('stripe_id', $stripeCustomerId)->first();

            if (! $user) {
                // Fallback attempt: If stripe_id mapping isn't populated yet, lookup via customer email if provided
                $customerEmail = $dataObject['customer_email'] ?? null;
                if ($customerEmail) {
                    $user = User::on('mysql')->where('email', $customerEmail)->first();
                }
            }

            if ($user) {
                // 4. Atomically persist or update the main active billing record inside the landlord database
                Subscription::on('mysql')->updateOrCreate(
                    [
                        'stripe_id' => $stripeSubscriptionId,
                    ],
                    [
                        'user_id' => $user->id,
                        'type' => 'main',
                        'stripe_status' => $stripeStatus,
                        'ends_at' => $endsAt,
                    ]
                );

                Log::info("Successfully synchronized Stripe subscription {$stripeSubscriptionId} to status {$stripeStatus} for User ID: {$user->id}");
            } else {
                Log::warning("Stripe Webhook aborted: No matching central user found for Customer ID: {$stripeCustomerId}");
            }
        }

        // 5. Always return a standard 200 success vector to notify Stripe servers to stop retrying the request
        return response()->json([
            'status' => 'success',
            'message' => 'Centralized webhook processed successfully.'
        ], 200);
    }
}

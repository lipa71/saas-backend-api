<?php

namespace App\Listeners;

use App\Events\InvoiceCreated;
use App\Models\Subscription;
use App\Models\MeterEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// Implement ShouldQueue to instantly push this processing into background docker queues
class LogInvoiceUsageToStripe  implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event and log enterprise metered consumption records.
     */
    public function handle(InvoiceCreated $event): void
    {
        $tenant = tenant();

        // 1. Skip calculation if request is outside tenant space or matches a freemium plan
        if (!$tenant || ($tenant->subscription_plan ?? 'free') === 'free') {
            return;
        }

        try {
            // 2. Query the landlord database explicitly to find the master billing profile
            $subscription = Subscription::on('mysql')
                ->where('user_id', $tenant->getAttribute('user_id'))
                ->where('type', 'main')
                ->where('stripe_status', 'active')
                ->first();

            if (! $subscription) {
                Log::warning("Metered Billing skipped: No active landlord subscription found for Owner ID: {$tenant->getAttribute('user_id')}");
                return;
            }

            // 3. Fetch the specific pricing record mapped to tracking invoice events (Metered item)
            $subscriptionItem = $subscription->items()->first();

            if (! $subscriptionItem) {
                Log::warning("Metered Billing skipped: Missing matching Stripe price item configuration for Subscription ID: {$subscription->id}");
                return;
            }

            // 4. Atomically persist the usage token inside our central landlord database
            $mockStripeEventId = 'mev_' . Str::random(24);

            MeterEvent::on('mysql')->create([
                'subscription_item_id' => $subscriptionItem->id,
                'stripe_event_id' => $mockStripeEventId,
                'quantity' => 1,
                'processed_at' => now(),
            ]);

            Log::info("Metered Billing Success: Recorded 1 invoice unit for Subscription Item ID: {$subscriptionItem->id} (Invoice: {$event->invoice->invoice_number})");

        } catch (\Exception $e) {
            Log::error("Metered Billing critical failure: " . $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class TenantBillingGate
{
    /**
     * Handle an incoming request and verify corporate Stripe subscription compliance.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Resolve the currently active tenant workspace initialized by Stancl/Tenancy
        $tenant = tenant();

        if (! $tenant) {
            return $next($request);
        }

        // 2. Bypass strict financial gates if the company is assigned to a standard freemium tier
        if (($tenant->subscription_plan ?? 'free') === 'free') {
            return $next($request);
        }

        // 3. Eager load the landlord owner along with their central Stripe subscription records
        $owner = User::on('mysql')->with('subscriptions')->find($tenant->user_id);

        if (! $owner) {
            return response()->json([
                'status' => 'error',
                'message' => 'Corporate workspace account manager mismatch. Access denied.'
            ], 403);
        }

        // 4. Fetch the active corporate main Stripe subscription payload
        $subscription = $owner->subscriptions()
            ->where('type', 'main')
            ->orderBy('created_at', 'desc')
            ->first();

        // 5. Enforce strict blocking rules if payments are delinquent, canceled, or expired
        if (! $subscription || !in_array($subscription->stripe_status, ['active', 'trialing'])) {
            return response()->json([
                'status' => 'error',
                'code' => 'PAYMENT_REQUIRED',
                'message' => 'Your workspace subscription has expired or has been suspended. Please update payment method.',
                'billing_portal_url' => 'http://localhost/api/billing/portal'
            ], 402); // 402 Payment Required is the explicit global web standard for unpaid SaaS domains
        }

        // 6. Subscription valid -> Pass execution down to the api controllers seamlessly
        return $next($request);
    }
}

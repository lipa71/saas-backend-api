<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Subscription;

class TenantBillingGate
{
    /**
     * Handle an incoming request and verify corporate Stripe subscription compliance.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Enforce strict cryptographic authentication barrier before any billing calculations
        if (!auth()->check()) {
            return response()->json([
                'status' => 'error',
                'code' => 'UNAUTHENTICATED',
                'message' => 'Authentication required. Please provide a valid workspace bearer token.'
            ], 401); // 401 Unauthorized is the explicit internet protocol standard
        }

        // 2. Resolve the currently active tenant workspace initialized by Stancl/Tenancy
        $tenant = tenant();

        if (!$tenant) {
            return $next($request);
        }

        // 3. Query the plan string attribute directly via unified tenant properties
        $currentPlan = $tenant->subscription_plan ?? 'free';

        if ($currentPlan === 'free') {
            return $next($request);
        }

        // 4. SECURE VERIFICATION: Check directly in the central database table via 'mysql' connection
        // Check if there is ANY active or trialing subscription for this specific workspace owner
        $hasActiveSubscription = Subscription::on('mysql')
            ->where('user_id', $tenant->getAttribute('user_id'))
            ->where('type', 'main')
            ->whereIn('stripe_status', ['active', 'trialing'])
            ->exists();

        // 5. Enforce strict blocking rules if no active payment subscription is registered in the landlord DB
        if (! $hasActiveSubscription) {
            return response()->json([
                'status' => 'error',
                'code' => 'PAYMENT_REQUIRED',
                'message' => 'Your workspace subscription has expired or has been suspended. Please update payment method.',
                'billing_portal_url' => 'http://localhost/api/billing/portal'
            ], 402); // 402 Payment Required is the explicit web standard for unpaid SaaS domains
        }

        // 6. Subscription valid -> Pass execution down to the api controllers seamlessly
        return $next($request);
    }
}

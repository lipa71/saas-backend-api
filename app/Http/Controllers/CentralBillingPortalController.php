<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CentralBillingPortalController extends Controller
{
    /**
     * Generate a secure, signed Stripe Customer Billing Portal session URL for workspace owners.
     */
    public function createPortalSession(Request $request): JsonResponse
    {
        // 1. Enforce strict token-based authorization checking before accessing corporate billing configurations
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'code' => 'UNAUTHENTICATED',
                'message' => 'Authentication required to manage corporate subscription preferences.'
            ], 401);
        }

        // 2. Locate the active enterprise company master blueprint to safely grab the Stripe Customer ID
        $tenantId = tenant('id');
        $company = Company::on('mysql')->find($tenantId);

        if (! $company) {
            return response()->json([
                'status' => 'error',
                'message' => 'Workspace configuration context could not be resolved.'
            ], 404);
        }

        // UNIFIED: Rigorously fetch the master stripe customer ID from the central landlord database connection
        $centralUser = User::on('mysql')
            ->where('email', $user->email)
            ->first();

        $stripeCustomerId = $centralUser ? $centralUser->stripe_id : null;

        if (! $stripeCustomerId) {
            return response()->json([
                'status' => 'error',
                'code' => 'BILLING_PROFILE_MISSING',
                'message' => 'No active Stripe payment profile registered for this workspace owner. Please activate subscription first.'
            ], 400);
        }

        // 3. Resolve the secure secret authorization keys compiled inside the environment variables
        $stripeSecret = config('services.stripe.secret') ?? env('STRIPE_SECRET');

        if (! $stripeSecret) {
            Log::critical("Billing Portal failure: Core STRIPE_SECRET credential parameter is missing in .env layout.");
            return response()->json([
                'status' => 'error',
                'message' => 'Payment gateway internal configuration breakdown.'
            ], 500);
        }

        try {
            // 4. Dispatch a raw authenticated HTTP payload to the official Stripe hosted billing endpoint vector
            $response = Http::withToken($stripeSecret)
                ->asForm()
                ->post('https://stripe.com', [
                    'customer' => $stripeCustomerId,
                    'return_url' => "http://{$tenantId}.localhost/dashboard", // Automatic return target back to tenant space
                ]);

            if ($response->failed()) {
                Log::error("Stripe API communication failure: " . $response->body());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to synchronize secure remote portal token session with Stripe.'
                ], 400);
            }

            $sessionData = $response->json();

            // 5. Return the newly generated secure routing URL destination directly to the client
            return response()->json([
                'status' => 'success',
                'billing_portal_url' => $sessionData['url']
            ], 200);

        } catch (\Exception $e) {
            Log::error("Billing Portal breakdown: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected structural breakdown occurred during payment portal generation.'
            ], 500);
        }
    }
}

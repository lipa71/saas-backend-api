<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Dynamic array of allowed roles passed from the routes file
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Safety check: ensure the user is authenticated via Sanctum first
        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // FIX: Verify by unique email instead of shared auto-incrementing ID
        $isCentralUser = tenancy()->central(fn () => \App\Models\User::where('email', $user->email)->exists());

        if ($isCentralUser) {
            return $next($request);
        }

        // Verify local tenant user relationship roles using the model helper
        if (! $user->hasRole($roles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden. You do not have the required permissions to perform this action.',
            ], 403);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Authenticate global platform entities (Owners/Super Admins) inside the central panel.
     */
    public function loginCentral(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Force lookup strictly inside the central database context
        $user = tenancy()->central(function () use ($request) {
            return User::where('email', $request->email)->first();
        });

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad credentials',
            ], 401);
        }

        // Generate token explicitly bound to the central repository
        $token = tenancy()->central(fn () => $user->createToken('saas-central-token')->plainTextToken);

        return response()->json([
            'status' => 'success',
            'account_type' => 'central_account',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Authenticate tenant sub-accounts and fallback to central database for workspace owners.
     */
    public function loginTenant(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // CRITICAL FIRST CHECK: Detect landlord owner bypass before any default auth guards execute
        if ($validated['email'] === 'owner@saas.com') {

            // Query the central landlord database connection explicitly for the master record
            $centralUser = User::on('mysql')
                ->where('email', $validated['email'])
                ->first();

            // Verify central hash stability
            if (!$centralUser || ! Hash::check($validated['password'], $centralUser->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid landlord owner credentials.'
                ], 401);
            }

            // Target the required local shadow account inside the active tenant environment
            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Workspace owner shadow configuration missing. Please re-run seeder.'
                ], 404);
            }

            // Issue clean unrestricted access token
            $token = $user->createToken('owner_token', ['*'])->plainTextToken;

            return response()->json([
                'status' => 'success',
                'token' => $token,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => 'owner',
                    'account_type' => 'tenant_owner_shadow'
                ]
            ], 200);
        }

        // STANDARD FLOW: Standard verification vector for regular enterprise employees
        if (! Auth::attempt($validated)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid tenant employee credentials.'
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user(); // UNIFIED: Eager type hint resolves code analysis warnings instantly

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles()->first()?->name ?? 'employee',
                'account_type' => 'tenant_sub_account'
            ]
        ], 200);
    }

    /**
     * Revoke the user's current token (Logout).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Tokens revoked successfully. User logged out.'
        ], 200);
    }
}

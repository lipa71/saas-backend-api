<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

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
     * Authenticate operational workspace members (Employees/Owner Shadows) inside the tenant application.
     */
    public function loginTenant(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Look up strictly within the active tenant database context
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad credentials',
            ], 401);
        }

        // Determine if this user represents the owner's local shadow account
        $isTenantOwner = (string) $user->id === (string) tenant('owner_id');

        // If it's the owner shadow copy, delegate password verification back to the central master authority
        if ($isTenantOwner) {
            $isPasswordValid = tenancy()->central(function () use ($request) {
                $centralUser = User::where('email', $request->email)->first();
                return $centralUser && Hash::check($request->password, $centralUser->password);
            });
        } else {
            // Standard local workspace employee verification
            $isPasswordValid = Hash::check($request->password, $user->password);
        }

        if (! $isPasswordValid) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad credentials',
            ], 401);
        }

        // Generate standard token in the current isolated tenant database
        $token = $user->createToken('saas-tenant-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'account_type' => $isTenantOwner ? 'tenant_owner' : 'tenant_sub_account',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles()->exists() ? $user->roles->first()?->name : null,
            ],
        ]);
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

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Authenticate both tenant sub-accounts (local) and global owners (central).
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // STEP 1: Attempt to find the user in the current isolated tenant database (Sub-account)
        $user = User::where('email', $request->email)->first();
        $isCentralUser = false;

        // STEP 2: If not found locally, fallback to look inside the central database (Global Owner)
        if (! $user) {
            $user = tenancy()->central(function () use ($request) {
                return User::where('email', $request->email)->first();
            });
            $isCentralUser = true;
        }

        // STEP 3: Verify user existence and check password credentials
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad credentials',
            ], 401);
        }

        // STEP 4: Generate token in the appropriate database context
        if ($isCentralUser) {
            // Generate and store token inside central database
            $token = tenancy()->central(fn () => $user->createToken('saas-api-token')->plainTextToken);
        } else {
            // Generate and store token inside isolated tenant database
            $token = $user->createToken('saas-api-token')->plainTextToken;
        }

        return response()->json([
            'status' => 'success',
            'account_type' => $isCentralUser ? 'central_owner' : 'tenant_sub_account',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}

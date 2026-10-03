<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class InvitationController extends Controller
{
    /**
     * Generate a secure token and store a new employee workspace invitation.
     */
    public function sendInvitation(Request $request): JsonResponse
    {
        // 1. Structural input validation inside the active tenant database context
        $validated = $request->validate([
            'email' => 'required|email',
            'role' => 'required|string|in:admin,manager,accountant,viewer', // Restrict to valid corporate roles
        ]);

        // 2. Compute secure tracking payloads with a strict 24-hour expiration threshold
        $token = Str::random(40);
        $expiresAt = now()->addHours(24);

        // 3. Atomically persist the record in the isolated tenant database
        $invitation = Invitation::create([
            'email' => $validated['email'],
            'token' => $token,
            'role' => $validated['role'],
            'expires_at' => $expiresAt,
            'is_accepted' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Workspace employee invitation generated successfully.',
            'data' => [
                'email' => $invitation->email,
                'role' => $invitation->role,
                'expires_at' => $invitation->expires_at->toIso8601String(),
                'activation_url' => "http://company_a.localhost/api/register/accept?token=" . $token
            ]
        ], 201);
    }

    /**
     * Validate the secure invitation token and dynamically register the new corporate employee.
     */
    public function acceptInvitation(Request $request): JsonResponse
    {
        // 1. Enforce strict input validation for password registration
        $validated = $request->validate([
            'token' => 'required|string',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 2. Fetch the invitation from the tenant context, ensuring it hasn't been used or expired
        $invitation = Invitation::where('token', $validated['token'])
            ->where('is_accepted', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return response()->json([
                'status' => 'error',
                'message' => 'The invitation token is invalid, has already been consumed, or has expired.'
            ], 410); // 410 Gone perfectly fits expired lifecycle tokens
        }

        // 3. Atomically create the user and attach the assigned role in a clean transaction
        $user = DB::transaction(function () use ($validated, $invitation) {

            // Create the operational employee account
            $newUser = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $invitation->email,
                'password' => bcrypt($validated['password']),
            ]);

            // Assign the predefined role from the invitation record
            $newUser->assignRole($invitation->role);

            // Mark the invitation as consumed to prevent replay attacks
            $invitation->update(['is_accepted' => true]);

            return $newUser;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Employee account activated successfully. You can now log in.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $invitation->role
            ]
        ], 201);
    }

}

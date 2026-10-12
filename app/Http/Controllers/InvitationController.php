<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmployeeInvitationMail;

class InvitationController extends Controller
{
    /**
     * Generate a secure token and store a new employee workspace invitation.
     */
    /**
     * Generate a unique cryptographic workspace invitation and dispatch a secure mail notification.
     */
    public function sendInvitation(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string|in:admin,manager,accountant,viewer',
        ]);

        // 1. Atomically persist the invitation record into the isolated tenant database
        $invitation = Invitation::create([
            'email' => $validated['email'],
            'role' => $validated['role'],
            'token' => Str::random(32),
            'expires_at' => now()->addDays(7),
        ]);

        // 2. Dispatch the luxury pure English notification mail directly to the invitee
        Mail::to($invitation->email)->send(new EmployeeInvitationMail($invitation));

        // 3. UNIFIED: Generate the exact structural payload requested by the HTTP Client script
        $activationUrl = 'http://' . tenant('id') . '.localhost/register/accept?token=' . $invitation->token;

        return response()->json([
            'status' => 'success',
            'message' => 'Workspace team invitation generated and dispatched successfully.',
            'data' => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'role' => $invitation->role,
                'token' => $invitation->token,
                'activation_url' => $activationUrl // UNIFIED: Perfectly mapped for JavaScript variable extraction
            ]
        ], 201); // 201 Created status code
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

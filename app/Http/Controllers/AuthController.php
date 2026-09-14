<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad credentials',
            ], 401);
        }

        $token = $user->createToken('saas-api-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => $user,
        ]);
    }
}

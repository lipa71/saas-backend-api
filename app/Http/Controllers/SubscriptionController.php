<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        // 1. Walidacja – oczekujemy identyfikatora ceny ze Stripe oraz metody płatności
        $request->validate([
            'price_id' => 'required|string',
            'payment_method' => 'required|string', // Fikcyjny token karty ze Stripe
        ]);

        // 2. Pobranie zalogowanego użytkownika (przez Sanctum)
        $user = $request->user();

        try {
            // 3. Jeśli użytkownik nie jest jeszcze klientem w Stripe, Cashier go stworzy.
            // Następnie tworzymy nową subskrypcję o nazwie 'default' na wybrany price_id.
            $user->newSubscription('default', $request->price_id)
                ->create($request->payment_method);

            return response()->json([
                'status' => 'success',
                'message' => 'Subscription activated successfully!',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment failed: '.$e->getMessage(),
            ], 402);
        }
    }
}

<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Endpoints (Unprotected Central API)
|--------------------------------------------------------------------------
*/

// 1. Application health check status
Route::get('/v1/status', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'SaaS Backend API is running perfectly',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// 2. Fallback auth check route for unauthenticated requests
Route::get('/v1/unauthorized', function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Unauthenticated. You must provide a valid Bearer Token.',
    ], 401);
})->name('login');

// 3. Central administration panel authentication (Global Owners)
Route::prefix('central')->group(function () {
    Route::post('/v1/login', [AuthController::class, 'loginCentral']);
});


/*
|--------------------------------------------------------------------------
| Protected Endpoints (Laravel Sanctum Secured Landlord API)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Core SaaS business domains configuration resource management
    Route::apiResource('companies', CompanyController::class);

    // Stripe checkout or corporate licensing plan management subscription hook
    Route::post('/v1/subscribe', [SubscriptionController::class, 'subscribe']);

    // ADDED HERE: Centralized administrator logout endpoint
    Route::post('/v1/logout', [AuthController::class, 'logout']);

});

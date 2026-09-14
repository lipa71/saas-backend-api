<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publiczne punkty końcowe (API Endpoints)
|--------------------------------------------------------------------------
*/

// 1. Status działania aplikacji (Ten endpoint testuje StatusApiTest)
Route::get('/v1/status', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'SaaS Backend API is running perfectly',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// 2. Logowanie użytkownika i generowanie tokenu
Route::post('/v1/login', [AuthController::class, 'login']);

// 3. Obsługa błędu autoryzacji (Przekierowanie, gdy brak tokenu)
Route::get('/v1/unauthorized', function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Unauthenticated. You must provide a valid Bearer Token.',
    ], 401);
})->name('login');

Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('companies', CompanyController::class);

    // NOWA TRASA PŁATNOŚCI:
    Route::post('/v1/subscribe', [SubscriptionController::class, 'subscribe']);

});

/*
|--------------------------------------------------------------------------
| Bezpieczne punkty końcowe chronione przez Laravel Sanctum
|--------------------------------------------------------------------------
*/

// Trasy wewnątrz tej grupy wymagają nagłówka "Authorization: Bearer TWÓJ_TOKEN"
Route::middleware('auth:sanctum')->group(function () {

    // Pełen zestaw CRUD dla firm (Ten endpoint testuje CompaniesApiTest)
    Route::apiResource('companies', CompanyController::class);

});

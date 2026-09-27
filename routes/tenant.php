<?php

declare(strict_types=1);

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });

    Route::prefix('api')->group(function () {
        // Authentication Route (Remains public so users can actually log in)
        Route::post('/login', [AuthController::class, 'login']);

        // Protected Routes (Only accessible with a valid Sanctum Token)
        Route::middleware('auth:sanctum,central_api')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);

            // Invoices Routes - Protected by dynamic corporate roles
            // 1. Fetching invoices is allowed for all authenticated company members
            Route::get('/invoices', [InvoiceController::class, 'index'])
                ->middleware('role:admin,manager,accountant,viewer');

            // 2. Creating an invoice is strictly restricted to admins and managers
            Route::post('/invoices', [InvoiceController::class, 'store'])
                ->middleware('role:admin,manager');

            // Temporarily placed for role middleware architecture verification
            Route::middleware('role:admin,manager')->get('/test-role-protection', function () {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Access granted! The middleware and role system are fully operational.',
                    'user' => auth()->user()->only(['id', 'email']),
                ]);
            });
        });
    });
});

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

        // Protected Invoices Routes (Only accessible with a valid Sanctum Token)
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/invoices', [InvoiceController::class, 'index']);
            Route::post('/invoices', [InvoiceController::class, 'store']);
        });
    });
});

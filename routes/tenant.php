<?php

declare(strict_types=1);

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvitationController;
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
        Route::post('/login', [AuthController::class, 'loginTenant']);

        // Public gateway for new employees to accept invitations and register
        Route::post('/register/accept', [InvitationController::class, 'acceptInvitation']);

        // Protected Routes (Only accessible with a valid Sanctum Token)
        Route::middleware('auth:sanctum,central_api')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);

            // Invoices Resource - Automated routing mapping for all CRUD actions
            Route::apiResource('invoices', InvoiceController::class);

            // Workspace Team Management invitations generation gateway
            Route::post('/invitations', [InvitationController::class, 'sendInvitation'])
                ->middleware('role:admin,manager');

            // Temporarily placed for role middleware architecture verification
//            Route::middleware('role:admin,manager')->get('/test-role-protection', function () {
//                return response()->json([
//                    'status' => 'success',
//                    'message' => 'Access granted! The middleware and role system are fully operational.',
//                    'user' => auth()->user()->only(['id', 'email']),
//                ]);
//            });
        });
    });
});

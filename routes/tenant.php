<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'api', // ZMIANA Z 'web' NA 'api' DLA RESTRYKCYJNYCH PUNKTÓW KOŃCOWYCH API SAAS
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is '.tenant('id');
    });

    Route::prefix('api')->group(function () {
        Route::get('/invoices', function () {
            return response()->json(DB::table('invoices')->get());
        });

        Route::post('/invoices', function (Request $request) {
            $validated = $request->validate([
                'invoice_number' => 'required|string',
                'customer_name' => 'required|string',
                'customer_tax_id' => 'required|string',
                'net_amount' => 'required|numeric',
                'vat_amount' => 'required|numeric',
                'gross_amount' => 'required|numeric',
                'due_date' => 'required|date',
            ]);

            DB::table('invoices')->insert(
                array_merge($validated, [
                    'status' => 'draft',
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );

            return response()->json(['message' => 'Invoice created successfully'], 201);
        });
    });
});

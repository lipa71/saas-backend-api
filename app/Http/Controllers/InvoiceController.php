<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    // 1. Pobieranie listy faktur z odizolowanej bazy danych
    public function index($tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);

        // Twarda inicjalizacja bazy klienckiej, dokładnie tak jak w Tinkerze
        tenancy()->initialize($tenant);

        // DIAGNOSTYKA: Zwracamy aktualne parametry połączenia z RAMu
        return response()->json([
            'debug_host' => config('database.connections.mysql.host'),
            'debug_port' => config('database.connections.mysql.port'),
            'debug_database' => config('database.connections.mysql.database'),
            'debug_username' => config('database.connections.mysql.username'),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => Invoice::latest()->get(),
        ], 200);
    }

    // 2. Wystawianie nowej faktury do odizolowanej bazy
    public function store(Request $request, $tenantId)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string',
            'customer_name' => 'required|string',
            'customer_tax_id' => 'required|string',
            'net_amount' => 'required|numeric',
            'vat_amount' => 'required|numeric',
            'gross_amount' => 'required|numeric',
            'due_date' => 'required|date',
        ]);

        $tenant = Tenant::findOrFail($tenantId);

        tenancy()->initialize($tenant);

        $invoice = Invoice::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice created successfully inside tenant database!',
            'data' => $invoice,
        ], 201);
    }
}

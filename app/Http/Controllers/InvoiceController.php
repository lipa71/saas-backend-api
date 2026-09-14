<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    // 1. Pobieranie listy faktur (baza jest już automatycznie przełączona przez middleware)
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => Invoice::latest()->get(),
        ], 200);
    }

    // 2. Wystawianie nowej faktury bezpośrednio do odizolowanej bazy klienta
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'customer_name' => 'required|string',
            'customer_tax_id' => 'required|string',
            'net_amount' => 'required|numeric',
            'vat_amount' => 'required|numeric',
            'gross_amount' => 'required|numeric',
            'due_date' => 'required|date',
        ]);

        $invoice = Invoice::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice created successfully inside tenant database!',
            'data' => $invoice,
        ], 201);
    }
}

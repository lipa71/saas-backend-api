<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        // Enforce the 'viewAny' policy rule checking roles dynamically
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::all();

        return response()->json([
            'status' => 'success',
            'data' => $invoices
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Enforce the 'create' policy rule checking roles dynamically
        $this->authorize('create', Invoice::class);

        // Your existing request validation logic
        $validated = $request->validate([
            'invoice_number' => 'required|string',
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
            'data' => $invoice
        ], 201);
    }
}

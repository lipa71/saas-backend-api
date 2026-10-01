<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
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
        $this->authorize('create', Invoice::class);

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

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice): JsonResponse
    {
        // Enforce view permissions - allowed for admin, manager, accountant, viewer
        $this->authorize('view', $invoice);

        return response()->json([
            'status' => 'success',
            'data' => $invoice
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        // Enforce update permissions - restricted for viewers
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'customer_name' => 'sometimes|required|string',
            'customer_tax_id' => 'sometimes|required|string',
            'net_amount' => 'sometimes|required|numeric',
            'vat_amount' => 'sometimes|required|numeric',
            'gross_amount' => 'sometimes|required|numeric',
            'due_date' => 'sometimes|required|date',
        ]);

        $invoice->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => $invoice
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        // Enforce destructive permissions - strictly allowed ONLY for admin and manager
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice deleted successfully.'
        ], 200);
    }
}

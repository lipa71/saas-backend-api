<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\VatRate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Invoice::class);

        // Eager load items and their corresponding VAT rate dictionary data
        $invoices = Invoice::with('items.vatRate')->get();

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

        // Advanced nested validation enforcing dictionary bounds for EU VAT rules
        $validated = $request->validate([
            'invoice_number' => 'required|string',
            'customer_name' => 'required|string',
            'customer_vat_number' => 'required|string',
            'due_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_net_price' => 'required|numeric|min:0',
            'items.*.vat_rate_id' => 'required|exists:vat_rates,id',
        ]);

        // Execute inside a database transaction to preserve multi-table atomic integrity
        $invoice = DB::transaction(function () use ($validated) {

            // 1. Create the base invoice header with temporary zero financial totals
            $invoice = Invoice::create([
                'invoice_number' => $validated['invoice_number'],
                'customer_name' => $validated['customer_name'],
                'customer_vat_number' => $validated['customer_vat_number'],
                'due_date' => $validated['due_date'],
                'net_amount' => 0.00,
                'vat_amount' => 0.00,
                'gross_amount' => 0.00,
            ]);

            // 2. Iterate and mathematically process each line item dynamically
            foreach ($validated['items'] as $itemData) {
                // Fetch the official immutable percentage rate directly from the dictionary source record
                $vatRateRecord = VatRate::findOrFail($itemData['vat_rate_id']);
                $taxPercentage = (float) $vatRateRecord->rate;

                $quantity = (int) $itemData['quantity'];
                $unitNetPrice = (float) $itemData['unit_net_price'];

                // Microfinancial math calculated strictly in Euro cents precision
                $netAmount = round($quantity * $unitNetPrice, 2);
                $vatAmount = round($netAmount * ($taxPercentage / 100), 2);
                $grossAmount = round($netAmount + $vatAmount, 2);

                // Persist the individual line item directly linked to the new invoice
                $invoice->items()->create([
                    'vat_rate_id' => $vatRateRecord->id,
                    'description' => $itemData['description'],
                    'quantity' => $quantity,
                    'unit_net_price' => $unitNetPrice,
                    'vat_rate' => $taxPercentage, // Preserved history snap copy
                    'net_amount' => $netAmount,
                    'vat_amount' => $vatAmount,
                    'gross_amount' => $grossAmount,
                ]);
            }

            // 3. Trigger the internal model aggregate recalculator to finish processing totals
            $invoice->recalculateTotals();

            return $invoice;
        });

        return response()->json([
            'status' => 'success',
            'data' => $invoice->load('items.vatRate')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        return response()->json([
            'status' => 'success',
            'data' => $invoice->load('items.vatRate')
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'customer_name' => 'sometimes|required|string',
            'customer_vat_number' => 'sometimes|required|string',
            'due_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|string',
        ]);

        $invoice->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => $invoice->load('items.vatRate')
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice deleted successfully.'
        ], 200);
    }
}

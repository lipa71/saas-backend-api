<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Http\Resources\InvoiceResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the tenant's invoices.
     */
    public function index(): AnonymousResourceCollection
    {
        // Return collection transformed via international InvoiceResource
        return InvoiceResource::collection(Invoice::latest()->get());
    }

    /**
     * Store a newly created tenant invoice in storage.
     */
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
            // Transform single created model via InvoiceResource
            'data' => new InvoiceResource($invoice),
        ], 201);
    }
}

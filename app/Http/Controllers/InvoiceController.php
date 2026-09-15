<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Http\Resources\InvoiceResource;
use App\Http\Requests\StoreInvoiceRequest; // Import the new request class
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the tenant's invoices.
     */
    public function index(): AnonymousResourceCollection
    {
        return InvoiceResource::collection(Invoice::latest()->get());
    }

    /**
     * Store a newly created tenant invoice in storage.
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        // The incoming data is already validated automatically before entering this method
        $invoice = Invoice::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice created successfully inside tenant database!',
            'data' => new InvoiceResource($invoice),
        ], 201);
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'status' => strtoupper($this->status),
            'due_date' => $this->due_date,

            // Client details mapped to clean English API fields
            'client' => [
                'name' => $this->customer_name,
                'tax_id' => $this->customer_tax_id,
            ],

            // Financial values formatted professionally with the EUR currency suffix
            'finances' => [
                'net_amount' => number_format((float) $this->net_amount, 2, '.', '') . ' EUR',
                'vat_amount' => number_format((float) $this->vat_amount, 2, '.', '') . ' EUR',
                'gross_amount' => number_format((float) $this->gross_amount, 2, '.', '') . ' EUR',
            ],

            // Standard timestamps converted to ISO-8601 strings
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

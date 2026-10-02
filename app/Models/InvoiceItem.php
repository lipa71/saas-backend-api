<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_id',
        'vat_rate_id',
        'description',
        'quantity',
        'unit_net_price',
        'vat_rate',
        'net_amount',
        'vat_amount',
        'gross_amount',
    ];

    /**
     * Get the parent invoice that owns this line item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the official dictionary VAT rate configuration applied to this line item.
     */
    public function vatRate(): BelongsTo
    {
        return $this->belongsTo(VatRate::class);
    }
}

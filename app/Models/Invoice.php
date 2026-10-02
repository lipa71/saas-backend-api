<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    /**
     * The default model attributes for database persistence stability.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'draft',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_number',
        'customer_name',
        'customer_vat_number',
        'net_amount',
        'vat_amount',
        'gross_amount',
        'due_date',
        'status',
    ];

    /**
     * Get the line items associated with this specific invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Recalculate and update the main invoice financial summaries based on its line items.
     */
    public function recalculateTotals(): void
    {
        $this->net_amount = $this->items()->sum('net_amount');
        $this->vat_amount = $this->items()->sum('vat_amount');
        $this->gross_amount = $this->items()->sum('gross_amount');
        $this->save();
    }
}

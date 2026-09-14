<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_name',
        'customer_tax_id',
        'net_amount',
        'vat_amount',
        'gross_amount',
        'status',
        'due_date',
    ];
}

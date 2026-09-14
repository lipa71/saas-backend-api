<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Unikalny numer faktury (np. FV/2026/08/01)
            $table->string('customer_name');            // Nazwa kontrahenta
            $table->string('customer_tax_id');          // NIP / Tax ID kontrahenta
            $table->decimal('net_amount', 10, 2);       // Kwota netto
            $table->decimal('vat_amount', 10, 2);       // Kwota VAT
            $table->decimal('gross_amount', 10, 2);     // Kwota brutto
            $table->string('status')->default('draft'); // Status: draft, sent, paid, overdue
            $table->date('due_date');                   // Termin płatności
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

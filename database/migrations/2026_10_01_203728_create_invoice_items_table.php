<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            // Atomic foreign keys linking to parents flawlessly
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vat_rate_id')->nullable()->constrained()->nullOnDelete();

            $table->string('description');
            $table->integer('quantity');
            $table->decimal('unit_net_price', 15, 2);
            $table->decimal('vat_rate', 5, 2); // Historical static copy of percentage
            $table->decimal('net_amount', 15, 2);
            $table->decimal('vat_amount', 15, 2);
            $table->decimal('gross_amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};

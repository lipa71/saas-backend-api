<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vat_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'Standard EU VAT (21%)'
            $table->decimal('rate', 5, 2); // e.g., 21.00
            $table->boolean('is_reverse_charge')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_rates');
    }
};

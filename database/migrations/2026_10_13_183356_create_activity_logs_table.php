<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for the tenant-specific isolated activity logs.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Link to the authenticated employee who performed the corporate operation
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Core descriptive attributes of the audited action
            $table->string('action'); // e.g., 'invoice.created', 'pdf.downloaded'
            $table->string('model_type')->nullable(); // e.g., 'App\Models\Invoice'
            $table->unsignedBigInteger('model_id')->nullable();

            // Context metadata capturing network locations and client types
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

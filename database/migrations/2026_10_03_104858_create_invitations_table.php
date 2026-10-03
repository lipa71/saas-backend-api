<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for the operational invitation flow.
     */
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();

            // The email address of the invited future employee
            $table->string('email');

            // Cryptographically secure unique token transmitted via URL parameter
            $table->string('token')->unique();

            // The target corporate business role to be assigned upon activation
            $table->string('role');

            // Lifecycle validation tracking timestamp
            $table->timestamp('expires_at');

            // State tracking flag to ensure tokens cannot be reused maliciously
            $table->boolean('is_accepted')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};

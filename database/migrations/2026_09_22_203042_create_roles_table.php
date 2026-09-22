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
        // Create the roles lookup table inside the isolated tenant database
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // System identifier, e.g., 'admin', 'accountant'
            $table->string('display_name');  // Human-readable name, e.g., 'Administrator', 'Accountant'
            $table->timestamps();
        });

        // Add foreign key relationship to the existing isolated users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::dropIfExists('roles');
    }
};

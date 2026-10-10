<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            // Native string identifier used for cross-database isolation mapping
            $table->string('id')->primary();

            // UNIFIED SaaS EXPANSION: Direct connection link to the landlord users table
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();

            // Enterprise Business Metas
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('subscription_plan')->default('free');

            // Native JSON data columns required by the core engine
            $table->json('data')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for the central landlord database meter events.
     */
    public function up(): void
    {
        Schema::create('meter_events', function (Blueprint $table) {
            $table->id();

            // Establish a rigid foreign connection directly to the central subscription items
            $table->foreignId('subscription_item_id')
                ->constrained('subscription_items')
                ->cascadeOnDelete();

            // Cryptographically secure or external Stripe token payload tracker
            $table->string('stripe_event_id')->unique();

            // The precise consumption usage factor increment unit
            $table->integer('quantity')->default(1);

            // Lifecycle validation timestamp tracking transmission success
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_events');
    }
};

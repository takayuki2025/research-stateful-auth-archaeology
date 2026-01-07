<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('processed_webhook_events', function (Blueprint $table) {
            $table->id();

            $table->string('provider', 50)->index();          // stripe
            $table->string('event_id', 191)->index();         // evt_xxx
            $table->string('event_type', 191)->index();       // payment_intent.succeeded ...
            $table->char('payload_hash', 64)->index();        // sha256(payload)
            $table->string('status', 32)->index(); // reserved | succeeded | ignored | failed
            $table->unsignedBigInteger('payment_id')->nullable()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('error_code', 64)->nullable();
            $table->string('error_message', 255)->nullable();


            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->unique(['provider', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_webhook_events');
    }
};

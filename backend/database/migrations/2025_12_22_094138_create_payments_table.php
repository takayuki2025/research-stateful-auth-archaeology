<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('shop_id')->index();
            $table->unsignedBigInteger('user_id')->index();

            $table->string('provider', 50)->index();     // stripe
            $table->string('method', 50)->index();       // card, konbini, ...
            $table->string('status', 50)->index();       // initiated, requires_action, succeeded, failed, cancelled

            $table->unsignedInteger('amount');
            $table->string('currency', 10);

            // External references (Stripe PI etc.)
            $table->string('provider_payment_id', 191)->nullable();
            $table->string('provider_customer_id', 191)->nullable()->index();

            // For konbini / instructions / receipt urls
            $table->json('method_details')->nullable();
            $table->json('instructions')->nullable();

            // audit / debug
            $table->json('meta')->nullable();

            $table->timestamps();

            // FK
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->cascadeOnDelete();

            // ✅ ★ これが今回の核心
            $table->unique(
                ['provider', 'provider_payment_id', 'order_id'],
                'uq_payments_provider_pi_order'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
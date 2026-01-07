<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {

        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Shop / Tenant
            $table->unsignedBigInteger('shop_id')->index();

            // Buyer
            $table->unsignedBigInteger('user_id')->index();

            // Status
            $table->string('status', 50)->index();

            // Totals
            $table->unsignedInteger('total_amount');
            $table->string('currency', 10);

            // Items snapshot
            $table->json('items_snapshot');

            // ★ Address snapshot（確定住所）
            $table->json('address_snapshot')->nullable();
            $table->timestamp('address_confirmed_at')->nullable();

            // Optional metadata
            $table->json('meta')->nullable();

            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

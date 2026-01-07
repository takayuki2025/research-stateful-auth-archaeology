<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {

            // PK
            $table->id();

            // マルチショップ前提
            $table->unsignedBigInteger('shop_id');
            $table->index('shop_id');

            // Order と 1:1
            $table->unsignedBigInteger('order_id')->unique();

            // 配送状態
            $table->string('status', 32);

            // 住所情報（JSON）
            $table->json('origin_address');
            $table->json('destination_address');

            // 到着予定
            $table->dateTime('eta')->nullable();

            $table->timestamps();

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('order_histories', function (Blueprint $table) {
            $table->id();

            // === スコープ ===
            $table->foreignId('shop_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();


            $table->unsignedBigInteger('order_id')->index();


            $table->foreignId('item_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // === Query 用スナップショット ===
            $table->string('item_name');
            $table->string('item_image')->nullable();
            $table->integer('price_amount');
            $table->string('price_currency', 10);

            // 支払い・配送スナップショット
            $table->string('payment_method', 32);
            $table->json('buy_address');


            $table->unsignedInteger('quantity')->default(1);


            // MyPage 用インデックス
            $table->index(['user_id', 'created_at']);
            $table->index(['shop_id', 'created_at']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_histories');
    }
};

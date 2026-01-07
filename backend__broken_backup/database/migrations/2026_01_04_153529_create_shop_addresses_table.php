<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shop_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shop_id')
                ->constrained()
                ->cascadeOnDelete();

            // ✅ Address ValueObject を JSON で保持
            $table->json('address');

            // デフォルト発送元かどうか
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            // 1 shop = 1 default address（Aフェーズ）
            $table->unique(['shop_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_addresses');
    }
};

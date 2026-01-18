<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('customer_wallets', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('shop_id')->nullable()->index(); // 将来のテナント拡張用（今はnullでもOK）

            // Stripe Customer
            $table->string('provider', 50)->default('stripe')->index();
            $table->string('provider_customer_id', 191)->nullable()->index();

            $table->string('status', 32)->default('active')->index(); // active/disabled

            $table->json('meta')->nullable();

            $table->timestamps();

            // 1ユーザーに1wallet（まずはこれで固定）
            $table->unique(['user_id', 'provider'], 'uq_customer_wallets_user_provider');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_wallets');
    }
};
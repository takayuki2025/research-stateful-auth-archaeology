<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('stored_payment_methods', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('wallet_id')->index();

            $table->string('provider', 50)->default('stripe')->index();
            $table->string('provider_payment_method_id', 191)->index(); // pm_xxx

            // 表示用（Stripeから取れる範囲のスナップショット）
            $table->string('brand', 50)->nullable();     // visa/mastercard...
            $table->string('last4', 4)->nullable();
            $table->unsignedTinyInteger('exp_month')->nullable();
            $table->unsignedSmallInteger('exp_year')->nullable();

            $table->boolean('is_default')->default(false)->index();
            $table->string('status', 32)->default('active')->index(); // active/detached

            $table->json('meta')->nullable();

            $table->timestamps();

            // 同一walletに同一pmは1回だけ
            $table->unique(['wallet_id', 'provider_payment_method_id'], 'uq_stored_pm_wallet_provider_pm');

            // FK（customer_wallets）
            $table->foreign('wallet_id')
                ->references('id')
                ->on('customer_wallets')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stored_payment_methods');
    }
};
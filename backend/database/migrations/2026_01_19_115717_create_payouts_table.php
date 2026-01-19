<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('account_id')->index();

            $table->bigInteger('amount'); // 正の値
            $table->string('currency', 10)->default('JPY');

            // requested | processing | paid | failed
            $table->string('status', 32)->index();

            // rails: stripe | bank | manual（最小は stripe/manual でもOK）
            $table->string('rail', 32)->default('stripe')->index();

            // 外部参照（Stripe payout id 等）
            $table->string('provider_payout_id', 191)->nullable()->index();

            $table->json('meta')->nullable();

            $table->dateTime('requested_at')->index();
            $table->dateTime('processed_at')->nullable()->index();
            $table->dateTime('paid_at')->nullable()->index();
            $table->dateTime('failed_at')->nullable()->index();

            $table->timestamps();

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
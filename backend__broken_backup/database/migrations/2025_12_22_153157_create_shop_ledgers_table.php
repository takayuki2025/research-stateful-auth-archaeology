<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shop_ledgers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('shop_id')->index();

            // sale | refund | fee | payout | adjustment
            $table->string('type', 32)->index();

            // signed amount（売上:+ / 返金・手数料:-）
            $table->integer('amount');

            $table->string('currency', 10);

            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('payment_id')->nullable()->index();

            $table->json('meta')->nullable();

            // ★ 追加（最重要）
            $table
                ->dateTime('occurred_at')
                ->index()
                ->comment('業務イベント発生時刻（決済確定・返金など）');

            // DB記録時刻（補助）
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_ledgers');
    }
};

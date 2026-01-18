<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ledger_postings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('shop_id')->index();

            // 冪等キー（Stripe event_id を入れる）
            $table->string('source_provider', 50)->index();     // stripe
            $table->string('source_event_id', 191)->index();    // evt_xxx

            // 参照（監査・トレース）
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('payment_id')->nullable()->index();

            // posting 種別（sale/refund/fee/payout...）
            $table->string('posting_type', 32)->index();

            $table->unsignedInteger('amount');   // 常に正の金額（entry側で貸借に分配）
            $table->string('currency', 10);

            $table->dateTime('occurred_at')->index(); // 業務発生時刻（あなたのJST運用ならJST）
            $table->json('meta')->nullable();

            $table->timestamps();

            // ✅ 冪等の本命
            $table->unique(
                ['source_provider', 'source_event_id'],
                'uq_ledger_postings_source_event'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_postings');
    }
};
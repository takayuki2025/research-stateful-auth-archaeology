<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('posting_id')->index();

            // 口座コード（v2-1は文字列で固定）
            $table->string('account_code', 64)->index(); // CASH_CLEARING, SALES_REVENUE, REFUND_EXPENSE ...

            // debit / credit
            $table->string('side', 10)->index();

            // 常に正の金額
            $table->unsignedInteger('amount');

            $table->string('currency', 10);

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->foreign('posting_id')
                ->references('id')
                ->on('ledger_postings')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
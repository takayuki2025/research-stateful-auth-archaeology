<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('balances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('account_id')->index();

            // v3-1: pending は 0 固定で開始（v3-2 holdで増える）
            $table->bigInteger('available_amount')->default(0);
            $table->bigInteger('pending_amount')->default(0);

            $table->string('currency', 10)->default('JPY');

            // 監査用：最後に再計算したタイミング
            $table->dateTime('calculated_at')->nullable()->index();

            $table->timestamps();

            $table->unique(['account_id'], 'uq_balances_account');

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            // owner 抽象（最小は shop）
            $table->string('account_owner_type', 50)->index(); // shop
            $table->unsignedBigInteger('account_owner_id')->index(); // shop_id

            $table->string('currency', 10)->default('JPY')->index();

            $table->timestamps();

            $table->unique(['account_owner_type', 'account_owner_id', 'currency'], 'uq_accounts_owner_currency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shop_ledgers', function (Blueprint $table) {
            $table->unique(['type', 'order_id', 'payment_id'], 'uq_shop_ledgers_type_order_payment');
        });
    }

    public function down(): void
    {
        Schema::table('shop_ledgers', function (Blueprint $table) {
            $table->dropUnique('uq_shop_ledgers_type_order_payment');
        });
    }
};
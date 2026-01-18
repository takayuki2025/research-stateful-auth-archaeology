<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('order_histories', function (Blueprint $table) {
            $table->unique(['order_id', 'item_id'], 'uq_order_histories_order_item');
        });
    }

    public function down(): void
    {
        Schema::table('order_histories', function (Blueprint $table) {
            $table->dropUnique('uq_order_histories_order_item');
        });
    }
};
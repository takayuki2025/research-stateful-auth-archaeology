<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_items', function (Blueprint $table) {
            // カートアイテムのID
            $table->id();


            $table->foreignId('shop_id')->nullable() // 💡 ここを追加
      ->constrained()
      ->cascadeOnDelete();


            // どのユーザーのカートか (usersテーブルへの外部キー)
            // ユーザーが削除されたらカートアイテムも削除
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // どの商品がカートにあるか (itemsテーブルへの外部キー)
            // 商品が削除されたらカートアイテムも削除
            $table->foreignId('item_id')
                ->constrained('items')
                ->cascadeOnDelete();

            // 数量 (カートに入っている商品の個数)
            $table->unsignedInteger('quantity')->default(1);

            // 重複を防ぐためのユニーク制約:
            // 1人のユーザーのカートには、同じ商品は1エントリのみ
            $table->unique(['user_id', 'item_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_items');
    }
}

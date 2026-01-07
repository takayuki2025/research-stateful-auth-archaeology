<?php

// database/migrations/2025_12_08_130000_add_foreign_keys_to_shops_and_users.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // 1. users テーブルに shop_id の外部キーを追加

    //     Schema::table('users', function (Blueprint $table) {
    //         $table->foreign('shop_id')
    //               ->references('id')
    //               ->on('shops')
    //               ->cascadeOnDelete();
    //     });

    //     // shops テーブルへの制約追加
    //     Schema::table('shops', function (Blueprint $table) {
    //         $table->foreign('owner_user_id')
    //               ->references('id')
    //               ->on('users')
    //               ->cascadeOnDelete();
    //     });

    // }

    // public function down(): void
    // {
    //     // down() メソッドでは外部キーを削除します
    //     Schema::table('users', function (Blueprint $table) {
    //         $table->dropConstrainedForeignId('shop_id');
    //     });

    //     Schema::table('shops', function (Blueprint $table) {
    //         $table->dropConstrainedForeignId('owner_user_id');
    //     });
    }
};

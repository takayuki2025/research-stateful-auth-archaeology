<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('catalog_sources', function (Blueprint $table) {
            // ✅ 承認済み（確定）状態
            $table->char('approved_hash', 64)->nullable()->after('last_hash');
            $table->timestamp('approved_at')->nullable()->after('approved_hash');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');

            // 将来：request_more_info の状態も持つならここに追加可能
            // $table->string('approval_status', 16)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('catalog_sources', function (Blueprint $table) {
            $table->dropColumn(['approved_hash', 'approved_at', 'approved_by']);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('legacy_items', function (Blueprint $table) {
    $table->id();

    /* =====================================================
     * 出所・系譜（Lineage）
     * ===================================================== */
    $table->string('source_system')->comment('legacy-db | csv | api | manual');
    $table->string('source_record_id')->nullable()
        ->comment('元システムのID（不明でもOK）');

    /* =====================================================
     * 生テキスト（最重要：AI入力）
     * ===================================================== */
    $table->string('raw_name')->nullable();
    $table->text('raw_description')->nullable();
    $table->string('raw_brand')->nullable();
    $table->string('raw_category')->nullable();
    $table->string('raw_condition')->nullable();

    /* =====================================================
     * 人間が書いたが、構造化されていない情報
     * ===================================================== */
    $table->json('raw_attributes')->nullable()
        ->comment('色・サイズ・素材・用途など雑多な情報');

    /* =====================================================
     * 数値だが信用できないもの
     * ===================================================== */
    $table->integer('raw_price')->nullable();
    $table->string('raw_currency', 3)->nullable();

    /* =====================================================
     * 画像・メディア（将来拡張）
     * ===================================================== */
    $table->json('raw_images')->nullable();

    /* =====================================================
     * 解析ステータス（Replay 管理）
     * ===================================================== */
    $table->enum('replay_status', [
        'pending',
        'analyzed',
        'reviewed',
        'confirmed',
        'rejected',
    ])->default('pending');

    $table->timestamp('replayed_at')->nullable();

    $table->timestamps();

    /* =====================================================
     * Index
     * ===================================================== */
    $table->index('source_system');
    $table->index('replay_status');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legacy_items');
    }
};

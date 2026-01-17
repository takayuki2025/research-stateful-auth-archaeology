<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('color_entities', function (Blueprint $table) {
    $table->id();

    // 正規化キー（検索・JOIN 用）
    $table->string('normalized_key')->index();

    // Canonical 論理名
    $table->string('canonical_name');

    // UI 表示名
    $table->string('display_name');

    // 同義語
    $table->json('synonyms_json')->nullable();

    // canonical merge 用
    $table->unsignedBigInteger('merged_to_id')->nullable()->index();
    $table->boolean('is_primary')->default(false)->index();

    // 学習・由来
    $table->decimal('confidence', 3, 2)->nullable();
    $table->string('created_from')->default('manual');
    $table->string('source', 16)->default('ai');

    $table->timestamps();

    $table->foreign('merged_to_id')
        ->references('id')
        ->on('color_entities')
        ->nullOnDelete();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('color_entities');
    }
};

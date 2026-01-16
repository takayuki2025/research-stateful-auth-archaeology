<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('brand_entities', function (Blueprint $table) {
            $table->id();

            // 表記揺れ吸収用（正規化キー）
            $table->string('normalized_key')->index();

            // canonical 名（SoT）
            $table->string('canonical_name');

            // UI 表示名
            $table->string('display_name');

            // synonym / alias（将来用）
            $table->json('synonyms_json')->nullable();

            // canonical 統合先（上位概念）
            $table->unsignedBigInteger('merged_to_id')->nullable();

            // 最終 canonical フラグ（安全装置）
            $table->boolean('is_primary')->default(false)->index();

            $table->decimal('confidence', 3, 2)->nullable();
            $table->string('created_from')->default('manual');
            $table->timestamps();

            // 制約
            $table->foreign('merged_to_id')
                ->references('id')
                ->on('brand_entities')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_entities');
    }
};
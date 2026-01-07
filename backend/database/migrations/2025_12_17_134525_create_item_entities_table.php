<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('item_entities', function (Blueprint $table) {
            $table->id();

            // =====================================================
            // Relations
            // =====================================================

            $table->unsignedBigInteger('item_id');

            // entity refs（v1：nullable / FK optional）
            $table->unsignedBigInteger('brand_entity_id')->nullable();
            $table->unsignedBigInteger('condition_entity_id')->nullable();
            $table->unsignedBigInteger('color_entity_id')->nullable();

            $table->json('confidence')->nullable();

            // =====================================================
            // Versioning / State
            // =====================================================

            // ★ v1 から is_latest を持つ（AtlasKernel 前提）
            $table->boolean('is_latest')->default(true);

            // AtlasKernel meta
            $table->string('generated_version')->default('v1');
            $table->timestamp('generated_at')->nullable();

            $table->timestamps();

            // =====================================================
            // Foreign Keys
            // =====================================================

            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->cascadeOnDelete();

            $table->foreign('brand_entity_id')
                ->references('id')
                ->on('brand_entities')
                ->nullOnDelete();

            // （condition_entities / color_entities は v1 では FK 省略でもOK）

            // =====================================================
            // Indexes
            // =====================================================

            // 最新スナップショット高速取得用
            $table->index(['item_id', 'is_latest']);

            // entity 検索用
            $table->index('brand_entity_id');
            $table->index('condition_entity_id');
            $table->index('color_entity_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_entities');
    }
};

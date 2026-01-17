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

            // 🔥 decision / request trace（必須）
            $table->unsignedBigInteger('analysis_request_id')->nullable();
            $table->unsignedBigInteger('review_decision_id')->nullable();

            // 🔥 差し替え・変更履歴（将来用）
            $table->unsignedBigInteger('replaced_item_entity_id')->nullable();

            // =====================================================
            // Entity refs（SoT snapshot）
            // =====================================================
            $table->unsignedBigInteger('brand_entity_id')->nullable();
            $table->unsignedBigInteger('condition_entity_id')->nullable();
            $table->unsignedBigInteger('color_entity_id')->nullable();

            // confidence（AI用：human_confirmed では null）
            $table->json('confidence')->nullable();

            // =====================================================
            // Versioning / State
            // =====================================================
            $table->boolean('is_latest')->default(true);

            // ai_provisional | human_confirmed | system_confirmed
            $table->string('source')->default('ai_provisional');

            // v3_confirmed / v3_provisional etc
            $table->string('generated_version')->default('v1');

            // approve / edit_confirm / manual_override / reject
            $table->string('decision_type')->nullable();

            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            // =====================================================
            // Index / Constraints
            // =====================================================
            $table->index(['item_id', 'is_latest']);

            // decision 単位冪等（最重要）
            $table->unique(['review_decision_id']);

            $table->index('analysis_request_id');
            $table->index('brand_entity_id');
            $table->index('condition_entity_id');
            $table->index('color_entity_id');

            // =====================================================
            // Foreign Keys
            // =====================================================
            $table->foreign('item_id')
                ->references('id')->on('items')
                ->cascadeOnDelete();

            $table->foreign('review_decision_id')
                ->references('id')->on('review_decisions')
                ->nullOnDelete();

            $table->foreign('replaced_item_entity_id')
                ->references('id')->on('item_entities')
                ->nullOnDelete();

            $table->foreign('brand_entity_id')
                ->references('id')->on('brand_entities')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_entities');
    }
};
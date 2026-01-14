<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('analysis_results', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('analysis_request_id');
            $table->unsignedBigInteger('item_id');

            // =========================
            // AFTER 候補（AI / 人）
            // =========================
            $table->string('brand_name')->nullable();
            $table->string('condition_name')->nullable();
            $table->string('color_name')->nullable();

            // =========================
            // v3 固定（判断材料）
            // =========================
            $table->json('confidence_map')->nullable();
            $table->decimal('overall_confidence', 4, 3)->nullable();

            // 根拠（AI説明 / Rule / future replay）
            $table->json('evidence')->nullable();

            // ★ 追加①：解析結果の出自
            // ai_provisional / human_confirmed / replayed_ai など
            $table->string('source')->nullable();

            // 技術状態（人間判断とは無関係）
            $table->enum('status', ['active', 'superseded', 'archived'])
                ->default('active');

            $table->timestamps();

            // =========================
            // Index / Constraint
            // =========================
            $table->unique('analysis_request_id'); // ★ 追加②
            $table->index('item_id');

            $table->foreign('analysis_request_id')
                ->references('id')
                ->on('analysis_requests')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_results');
    }
};
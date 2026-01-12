<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('analysis_results', function (Blueprint $table) {
            $table->id();

            /**
             * ★ Bフェーズ確定：AnalysisRequest 単位
             */
            $table->unsignedBigInteger('analysis_request_id');

            /**
             * item 参照（集計・検索用）
             */
            $table->unsignedBigInteger('item_id');

            /**
             * AtlasKernel 正式結果（完全スナップショット）
             */
            $table->json('payload');

            /**
             * Review/UI 用（冗長・将来 drop 可）
             */
            $table->json('tags')->nullable();
            $table->json('confidence')->nullable();
            $table->string('generated_version')->nullable();
            $table->text('raw_text')->nullable();

            /**
             * active / rejected / superseded
             */
            $table->string('status')->default('active');

            $table->timestamps();

            $table->index(['analysis_request_id', 'status']);
            $table->index(['item_id', 'status']);

            $table->foreign('analysis_request_id')
                ->references('id')
                ->on('analysis_requests')
                ->cascadeOnDelete();

            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_results');
    }
};
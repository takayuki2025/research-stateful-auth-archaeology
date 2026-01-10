<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('analysis_results', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('item_id');

            /**
             * v3: AtlasKernel 正式結果（完全スナップショット）
             */
            $table->json('payload');

            /**
             * v2互換 / 検索・集計用の冗長カラム
             * （将来 drop 可能）
             */
            $table->json('tags')->nullable();
            $table->json('confidence')->nullable();
            $table->string('generated_version')->nullable();
            $table->text('raw_text')->nullable();

            // active / rejected / superseded
            $table->string('status')->default('active');

            $table->timestamps();

            $table->index(['item_id', 'status']);

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
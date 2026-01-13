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

            // After 候補
            $table->string('brand_name')->nullable();
            $table->string('condition_name')->nullable();
            $table->string('color_name')->nullable();

            // v3固定
            $table->json('confidence_map')->nullable();
            $table->decimal('overall_confidence', 4, 3)->nullable();

            // 根拠（AI / Rule / future replay）
            $table->json('evidence')->nullable();

            // 技術状態のみ
            $table->enum('status', ['active', 'superseded', 'archived'])
                ->default('active');

            $table->timestamps();

            $table->index(['analysis_request_id']);
            $table->index(['item_id']);

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
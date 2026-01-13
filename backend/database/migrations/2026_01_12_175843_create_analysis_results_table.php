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

    // 正規化候補（After）
    $table->string('brand_name')->nullable();
    $table->string('condition_name')->nullable();
    $table->string('color_name')->nullable();

    // confidence
    $table->decimal('brand_confidence', 4, 3)->nullable();
    $table->decimal('condition_confidence', 4, 3)->nullable();
    $table->decimal('color_confidence', 4, 3)->nullable();

    // 解析根拠（将来AI用）
    $table->json('evidence')->nullable();

    $table->timestamps();

    $table->index(['analysis_request_id']);
    $table->index(['item_id']);

    $table->foreign('analysis_request_id')
        ->references('id')->on('analysis_requests')
        ->cascadeOnDelete();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_results');
    }
};
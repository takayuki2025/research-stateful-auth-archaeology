<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('review_decisions', function (Blueprint $table) {
    $table->id();

    // ★ Bフェーズ主語（必須）
    $table->unsignedBigInteger('analysis_request_id');

    // ★ Cフェーズ主語（将来用）
    $table->string('subject_type')->nullable();
    $table->unsignedBigInteger('subject_id')->nullable();

    // 判断種別
    $table->string('decision_type'); // approve | reject | edit_confirm | system_approve

    // 理由・注釈
    $table->string('decision_reason')->nullable();
    $table->text('note')->nullable();

    // スナップショット
    $table->json('before_snapshot')->nullable();
    $table->json('after_snapshot')->nullable();

    // 判断者
    $table->string('decided_by_type')->default('human');
    $table->unsignedBigInteger('decided_by')->nullable();

    // 判断時刻
    $table->timestamp('decided_at');

    $table->timestamps();

    $table->index(['analysis_request_id']);
    $table->index(['subject_type', 'subject_id']);
    $table->index(['decision_type']);
    $table->index(['decided_at']);

    $table->foreign('analysis_request_id')
        ->references('id')
        ->on('analysis_requests')
        ->cascadeOnDelete();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('review_decisions');
    }
};
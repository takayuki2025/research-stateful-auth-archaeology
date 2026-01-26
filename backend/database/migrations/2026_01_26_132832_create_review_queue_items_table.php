<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('review_queue_items', function (Blueprint $table) {
            $table->id();

            // v3.3 MultiProject（未導入でもnullableで先に持つ）
            $table->unsignedBigInteger('project_id')->nullable()->index();

            // atlas / providerintel / psp_change など
            $table->string('queue_type', 32)->index();

            // catalog_source / extracted_document / proposed_change / analysis_request など
            $table->string('ref_type', 64)->index();
            $table->unsignedBigInteger('ref_id')->index();

            // pending / in_review / decided / archived
            $table->string('status', 16)->default('pending')->index();

            // low(10) normal(50) high(90)
            $table->unsignedSmallInteger('priority')->default(50)->index();

            // 差分要約など（v4でdocument_diffsへ移しても良いがMVPではここでOK）
            $table->json('summary_json')->nullable();

            // 監査/運用
            $table->string('decided_action', 32)->nullable(); // approve/reject/request_more_info
            $table->unsignedBigInteger('decided_by')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            // 同じ対象を何度も積まない（最低限の冪等）
            $table->unique(['queue_type', 'ref_type', 'ref_id', 'status'], 'uk_queue_ref_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_queue_items');
    }
};
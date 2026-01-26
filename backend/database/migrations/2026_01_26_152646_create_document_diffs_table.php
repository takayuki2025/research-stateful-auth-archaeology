<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('document_diffs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('project_id')->nullable()->index();

            $table->unsignedBigInteger('before_document_id')->nullable()->index();
            $table->unsignedBigInteger('after_document_id')->index();

            // まずは要約JSON（差分箇所の行数/簡易サマリ）
            $table->json('diff_summary_json')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('before_document_id')
                ->references('id')->on('extracted_documents')
                ->nullOnDelete();

            $table->foreign('after_document_id')
                ->references('id')->on('extracted_documents')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_diffs');
    }
};
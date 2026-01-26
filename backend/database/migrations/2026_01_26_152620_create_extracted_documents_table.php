<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('extracted_documents', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('project_id')->nullable()->index();

            // providerintel / policy / etc
            $table->string('domain', 32)->default('providerintel')->index();

            // html / pdf / ocr_text
            $table->string('source_type', 16)->index();

            // URLベース。将来は evidence_id (image/pdf) を足す
            $table->text('source_url')->nullable();
            $table->char('source_url_hash', 64)->nullable()->index();

            // 本文（root asset）
            $table->longText('content_text');

            // 冪等（同一本文なら同じhash）
            $table->char('content_hash', 64)->index();

            $table->timestamp('extracted_at')->nullable();

            $table->timestamps();

            // 同一hashの重複保存を抑制（緩いunique）
            $table->unique(['domain', 'content_hash'], 'uk_domain_content_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracted_documents');
    }
};
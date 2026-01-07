<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('item_entity_audits', function (Blueprint $table) {
            $table->id();

            // 対象 item
            $table->unsignedBigInteger('item_id');

            // AtlasKernel 判定ログ
            $table->json('confidence')->nullable();
            $table->text('raw_text')->nullable();

            // meta
            $table->string('generated_version')->default('v1.5');
            $table->timestamps();

            // FK
            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->cascadeOnDelete();

            $table->index('item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_entity_audits');
    }
};

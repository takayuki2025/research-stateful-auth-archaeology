<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {

        Schema::create('brand_entities', function (Blueprint $table) {
            $table->id();

            // 比較・検索・JOIN 用（正規化済み）
            $table->string('canonical_name')->unique();

            // UI 表示用
            $table->string('display_name');

            // alias / synonym（将来用）
            $table->json('synonyms_json')->nullable();

            $table->decimal('confidence', 3, 2)->nullable();
            $table->string('created_from')->default('manual');
            $table->timestamps();

            $table->index('canonical_name');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('brand_entities');
    }
};

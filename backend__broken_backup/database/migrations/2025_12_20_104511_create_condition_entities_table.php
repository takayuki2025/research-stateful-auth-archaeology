<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('condition_entities', function (Blueprint $table) {
            $table->id();

            // 正規化された正式名
            $table->string('canonical_name');

            // 検索・同一判定用（lower 済み）
            // $table->string('normalized_key')->unique();

            // AtlasKernel の信頼度
            $table->float('confidence')->nullable();

            // 生成元（atlaskernel_v1 など）
            $table->string('created_from')->default('atlaskernel_v1');

            $table->timestamps();

            // index
            $table->index('canonical_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condition_entities');
    }
};

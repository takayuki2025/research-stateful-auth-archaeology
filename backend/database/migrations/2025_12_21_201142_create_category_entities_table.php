<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('category_entities', function (Blueprint $table) {
            $table->id();

            $table->string('canonical_name')->comment('正規化カテゴリ名');
            $table->string('display_name')->comment('表示用カテゴリ名');

            // 将来の階層対応
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('category_entities')
                ->nullOnDelete();

            $table->float('confidence')->default(1.0);
            $table->string('created_from')->default('manual');

            $table->timestamps();

            $table->unique('canonical_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_entities');
    }
};

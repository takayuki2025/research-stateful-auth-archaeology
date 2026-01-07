<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('item_entity_tags', function (Blueprint $table) {
            $table->id();

            // =========================
            // Relations
            // =========================
            $table->unsignedBigInteger('item_id');

            // brand / condition / color / category etc
            $table->string('tag_type');   // 'brand' | 'condition' | 'color'
            $table->unsignedBigInteger('entity_id')->nullable();

            // 表示用 canonical（entity 無くても表示可能）
            $table->string('display_name');

            // confidence / meta
            $table->float('confidence')->default(0.0);

            $table->timestamps();

            // =========================
            // Index / FK
            // =========================
            $table->index(['item_id', 'tag_type']);
            $table->index(['tag_type', 'entity_id']);

            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_entity_tags');
    }
};

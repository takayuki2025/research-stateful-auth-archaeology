<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('item_entity_tags', function (Blueprint $table) {
            $table->id();

            // v3 正式：item_id は持たない
            $table->unsignedBigInteger('item_entity_id');

            // tag meta
            $table->string('tag_type');          // brand / condition / color / category
            $table->unsignedBigInteger('entity_id')->nullable();

            // 表示・信頼度
            $table->string('display_name');
            $table->float('confidence')->default(1.0);

            $table->timestamps();

            $table->index(['item_entity_id', 'tag_type']);

            $table->foreign('item_entity_id')
                ->references('id')
                ->on('item_entities')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_entity_tags');
    }
};
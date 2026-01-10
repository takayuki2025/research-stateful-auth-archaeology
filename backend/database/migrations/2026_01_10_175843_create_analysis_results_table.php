<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('analysis_results', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('item_id');

            $table->json('tags');
            $table->json('confidence');
            $table->string('generated_version');
            $table->text('raw_text');

            // active / rejected / superseded
            $table->string('status')->default('active');

            $table->timestamps();

            $table->index(['item_id', 'status']);

            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_results');
    }
};
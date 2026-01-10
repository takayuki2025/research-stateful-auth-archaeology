<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('review_decisions', function (Blueprint $table) {
            $table->id();

            $table->string('subject_type'); // 'item'
            $table->unsignedBigInteger('subject_id'); // item_id

            $table->string('decision_type'); // confirm | edit_confirm | reject

            $table->json('before_snapshot')->nullable();
            $table->json('after_snapshot')->nullable();

            $table->unsignedBigInteger('decided_by');
            $table->text('note')->nullable();
            $table->timestamp('decided_at');

            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['decision_type']);
            $table->index(['decided_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_decisions');
    }
};
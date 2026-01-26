<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('review_requests_for_info', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('review_queue_item_id')->index();

            // open / closed
            $table->string('status', 16)->default('open')->index();

            // checklist payload (structured)
            $table->json('checklist_json');

            // who requested (x-admin-key => null でもOK)
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->timestamp('requested_at')->nullable();

            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            $table->foreign('review_queue_item_id')
                ->references('id')
                ->on('review_queue_items')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_requests_for_info');
    }
};
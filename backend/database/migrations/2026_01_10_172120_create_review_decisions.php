<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('review_decisions', function (Blueprint $table) {
            $table->id();

            /* =========================
             * Relation
             * ========================= */
            $table->unsignedBigInteger('analysis_request_id');

            /* =========================
             * Decision subject (future-proof)
             * ========================= */
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            /* =========================
             * Decision
             * ========================= */
            $table->string('decision_type')
                ->comment('approve | reject | edit_confirm | system_approve');

            $table->string('decision_reason')->nullable();
            $table->text('note')->nullable();

            /* =========================
             * Snapshots
             * ========================= */
            $table->json('before_snapshot')->nullable();
            $table->json('after_snapshot')->nullable();

            /* =========================
             * Actor
             * ========================= */
            $table->string('decided_by_type', 16)
                ->default('human'); // human | system | policy
            $table->unsignedBigInteger('decided_by')->nullable();

            $table->timestamp('decided_at');

            $table->timestamps();

            /* =========================
             * Index / FK
             * ========================= */
            $table->index(['analysis_request_id']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['decision_type']);
            $table->index(['decided_at']);

            $table->foreign('analysis_request_id')
                ->references('id')
                ->on('analysis_requests')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_decisions');
    }
};

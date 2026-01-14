<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_requests', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->uuid('item_draft_id')->nullable();

            /* =========================
             * Identity / Scope
             * ========================= */
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('item_id');

            /* =========================
             * Replay lineage
             * ========================= */
            $table->unsignedBigInteger('original_request_id')->nullable()
                ->comment('Root request id for replay chain');
            $table->unsignedSmallInteger('replay_index')->nullable()
                ->comment('Incremental replay index');

            /* =========================
             * Trigger information
             * ========================= */
            $table->string('triggered_by_type', 16)
                ->default('system')
                ->comment('system | human | policy');
            $table->unsignedBigInteger('triggered_by')->nullable();
            $table->string('trigger_reason', 255)->nullable();

            /* =========================
 * Analysis specification
 * ========================= */
$table->string('analysis_version', 32)
    ->comment('Actual analyzer version used');

$table->string('requested_version', 32)->nullable()
    ->comment('Requested analyzer version (optional)');

/**
 * 🔑 v3 必須：解析入力の Source of Truth
 */
$table->text('raw_text')
    ->comment('Normalized raw input text used for analysis');

$table->char('payload_hash', 64)
    ->comment('Hash of input payload for idempotency');

$table->string('idempotency_key', 255)
    ->comment('External idempotency key');

            /* =========================
             * Execution state
             * ========================= */
            $table->enum('status', [
                'pending',
                'running',
                'done',
                'failed',
                'superseded'
            ])->default('pending');

            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();

            /* =========================
             * Error / retry
             * ========================= */
            $table->string('error_code', 64)->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('retry_count')->default(0);

            $table->timestamps();

            /* =========================
             * Index
             * ========================= */
            $table->unique('idempotency_key', 'uk_idempotency');
            $table->index(['tenant_id', 'item_id'], 'idx_item');
            $table->index(['status'], 'idx_status');
            $table->index(['analysis_version'], 'idx_version');
            $table->index(['original_request_id'], 'idx_original_request');

            $table->foreign('original_request_id')
                ->references('id')
                ->on('analysis_requests')
                ->nullOnDelete();
        });

        /* =========================
         * Request Event Ledger
         * ========================= */
        Schema::create('analysis_request_events', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('analysis_request_id');
            $table->string('event_type', 64);
            $table->json('event_payload')->nullable();
            $table->dateTime('created_at');

            $table->foreign('analysis_request_id', 'fk_analysis_event_request')
                ->references('id')
                ->on('analysis_requests')
                ->cascadeOnDelete();

            $table->index(['analysis_request_id', 'event_type'], 'idx_request_event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_request_events');
        Schema::dropIfExists('analysis_requests');
    }
};
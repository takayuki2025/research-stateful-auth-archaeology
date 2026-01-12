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

            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('item_id');

            $table->string('analysis_version', 32);
            $table->char('payload_hash', 64);
            $table->string('idempotency_key', 255);

            $table->enum('status', ['pending', 'running', 'done', 'failed'])->default('pending');

            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();

            $table->string('error_code', 64)->nullable();
            $table->text('error_message')->nullable();

            $table->unsignedInteger('retry_count')->default(0);

            $table->timestamps();

            $table->unique('idempotency_key', 'uk_idempotency');
            $table->index(['tenant_id', 'item_id'], 'idx_item');
            $table->index(['status'], 'idx_status');
            $table->index(['analysis_version'], 'idx_version');
        });

        Schema::create('analysis_request_events', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('analysis_request_id');
            $table->string('event_type', 64);
            $table->json('event_payload')->nullable();
            $table->dateTime('created_at');

            $table->foreign('analysis_request_id', 'fk_analysis_event_request')
                ->references('id')->on('analysis_requests')
                ->onDelete('cascade');

            $table->index(['analysis_request_id', 'event_type'], 'idx_request_event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_request_events');
        Schema::dropIfExists('analysis_requests');
    }
};
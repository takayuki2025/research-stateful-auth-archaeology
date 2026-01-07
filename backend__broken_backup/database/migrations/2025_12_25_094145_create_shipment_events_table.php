<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {

    public function up(): void
    {
        Schema::create('shipment_events', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('shipment_id')->index();

            // Event 種別（packed / shipped / delivered など）
            $table->string('type', 100);

            /**
             * Event 付随情報（将来拡張用）
             * - v1: null / []
             * - v2: 配送会社 / tracking_no / 再配送理由 / webhook payload
             */
            $table->json('meta')->nullable();

            // Event 発生時刻（業務上の真実）
            $table->timestamp('occurred_at')->index();

            // 管理用
            $table->timestamps();

            $table->foreign('shipment_id')
                ->references('id')
                ->on('shipments')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_events');
    }
};
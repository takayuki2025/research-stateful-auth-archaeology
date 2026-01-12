<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('decision_ledgers', function (Blueprint $table) {
            $table->id();

            // 決定対象（Bフェーズの正解：request起点）
            $table->unsignedBigInteger('analysis_request_id');

            // 決定者（必須：責任）
            $table->unsignedBigInteger('decided_user_id');

            // decided_by は「人間 / システム」などの分類（Aルートでは human 固定でOK）
            $table->string('decided_by', 32)->default('human');

            // approved / rejected
            $table->string('decision', 32);

            // 任意：理由（UIで入力したいなら）
            $table->text('reason')->nullable();

            // 決定日時（監査）
            $table->timestamp('decided_at');

            $table->timestamps();

            // 1 request に 1 決定（Aルートの割り切り：後でBに拡張）
            $table->unique('analysis_request_id');

            $table->index(['decided_user_id', 'decided_at']);

            // FKs
            $table->foreign('analysis_request_id')
                ->references('id')
                ->on('analysis_requests')
                ->cascadeOnDelete();

            $table->foreign('decided_user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decision_ledgers');
    }
};
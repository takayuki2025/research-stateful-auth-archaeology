<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('learning_candidates', function (Blueprint $table) {
            $table->id();

            $table->string('entity_type');    // brand / condition / color
            $table->string('proposed_name');  // 人が確定/提案した名称（または採用した名称）
            $table->string('normalized_key'); // 正規化キー（重複検知用）

            // ★追加（運営前に入れる）
            $table->string('decision_type')->nullable();         // approve / manual_override / edit_confirm / reject etc.
            $table->unsignedBigInteger('entity_id')->nullable(); // 選択/生成された canonical entity のID

            $table->string('source'); // ai | human | mixed
            $table->decimal('confidence', 5, 3)->nullable();

            $table->unsignedBigInteger('analysis_request_id')->nullable();
            $table->unsignedBigInteger('review_decision_id')->nullable();

            $table->string('status')->default('pending'); // pending / promoted / rejected

            $table->timestamps();

            $table->index(['entity_type', 'normalized_key']);
            $table->index('analysis_request_id');
            $table->index('review_decision_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_candidates');
    }
};

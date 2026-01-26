<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        // 1) 生成列 active_status を追加（pending/in_review のときだけ値を持つ）
        DB::statement("
            ALTER TABLE review_queue_items
            ADD COLUMN active_status VARCHAR(32)
            GENERATED ALWAYS AS (
              CASE
                WHEN status IN ('pending','in_review') THEN status
                ELSE NULL
              END
            ) STORED
        ");

        // 2) 既存のユニークを落とす
        DB::statement("ALTER TABLE review_queue_items DROP INDEX uk_queue_ref_status");

        // 3) アクティブ状態だけ唯一化するユニークを追加
        DB::statement("
            ALTER TABLE review_queue_items
            ADD UNIQUE KEY uk_queue_ref_active_status (queue_type, ref_type, ref_id, active_status)
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE review_queue_items DROP INDEX uk_queue_ref_active_status");
        DB::statement("ALTER TABLE review_queue_items DROP COLUMN active_status");
        DB::statement("
            ALTER TABLE review_queue_items
            ADD UNIQUE KEY uk_queue_ref_status (queue_type, ref_type, ref_id, status)
        ");
    }
};
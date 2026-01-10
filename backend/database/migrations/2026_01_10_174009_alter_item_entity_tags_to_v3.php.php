<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('item_entity_tags', function (Blueprint $table) {
            // すでに存在する場合は手動調整が必要（環境差があるため）
            if (!Schema::hasColumn('item_entity_tags', 'item_entity_id')) {
                $table->unsignedBigInteger('item_entity_id')->nullable()->after('id');
            }
            if (Schema::hasColumn('item_entity_tags', 'item_id')) {
                // v3 では使わないが、互換のため残しても良い
                // $table->dropColumn('item_id');
            }
            if (Schema::hasColumn('item_entity_tags', 'tag_type') === false && Schema::hasColumn('item_entity_tags', 'entity_type')) {
                // 旧名がある場合
                $table->string('tag_type')->nullable()->after('item_entity_id');
            }
            if (!Schema::hasColumn('item_entity_tags', 'display_name')) {
                $table->string('display_name')->nullable()->after('entity_id');
            }
        });

        // 既存データ移行（可能な範囲）
        // item_id -> item_entity_id を埋める（latest entity へ寄せる）
        \DB::statement("
            UPDATE item_entity_tags t
            JOIN item_entities e ON e.item_id = t.item_id AND e.is_latest = 1
            SET t.item_entity_id = COALESCE(t.item_entity_id, e.id)
            WHERE t.item_entity_id IS NULL AND t.item_id IS NOT NULL
        ");

        // entity_type -> tag_type を埋める（旧カラムがある場合）
        if (Schema::hasColumn('item_entity_tags', 'entity_type') && Schema::hasColumn('item_entity_tags', 'tag_type')) {
            \DB::statement("
                UPDATE item_entity_tags
                SET tag_type = COALESCE(tag_type, entity_type)
            ");
        }

        // display_name が無い既存データの暫定埋め（entity_id しか無い場合）
        if (Schema::hasColumn('item_entity_tags', 'display_name')) {
            \DB::statement("
                UPDATE item_entity_tags
                SET display_name = COALESCE(display_name, '')
                WHERE display_name IS NULL
            ");
        }

        // NOT NULL 制約を付ける（最後に）
        Schema::table('item_entity_tags', function (Blueprint $table) {
            // 既存の null が残っている可能性があるので、運用に合わせて調整してください
            // v3 本番では display_name は NOT NULL 推奨
        });

        // index
        Schema::table('item_entity_tags', function (Blueprint $table) {
            if (!Schema::hasColumn('item_entity_tags', 'tag_type')) return;
            // すでに同名 index がある可能性があるので、必要に応じて手動で
            // $table->index(['item_entity_id', 'tag_type']);
        });
    }

    public function down(): void
    {
        // v3移行後は down を無理に戻さない方針でも良い
    }
};
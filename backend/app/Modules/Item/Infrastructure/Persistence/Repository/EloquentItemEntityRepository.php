<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Modules\Item\Domain\Repository\ItemEntityRepository;
use Illuminate\Support\Facades\DB;

final class EloquentItemEntityRepository implements ItemEntityRepository
{
    /**
     * v3固定
     * - 現在の latest をすべて false にする
     * - update してよいのは is_latest のみ
     */
    public function markAllAsNotLatest(int $itemId): void
    {
        DB::table('item_entities')
            ->where('item_id', $itemId)
            ->where('is_latest', true)
            ->update([
                'is_latest'  => false,
                'updated_at' => now(),
            ]);
    }

    /**
     * v3固定
     * - append-only
     * - source / generated_version は必須
     * - confidence は DECIMAL（JSON禁止）
     */
    public function create(array $attrs): int
    {
        return (int) DB::table('item_entities')->insertGetId([
            'item_id'             => $attrs['item_id'],
            'brand_entity_id'     => $attrs['brand_entity_id'] ?? null,
            'condition_entity_id' => $attrs['condition_entity_id'] ?? null,
            'color_entity_id'     => $attrs['color_entity_id'] ?? null,

            // v3 핵심
            'source'              => $attrs['source'], // 必須
            'generated_version'   => $attrs['generated_version'], // 必須

            // confidence は optional（AI のみ）
            'confidence'          => $attrs['confidence'] ?? null,

            'is_latest'           => (bool)($attrs['is_latest'] ?? true),
            'generated_at'        => $attrs['generated_at'] ?? now(),

            'created_at'          => now(),
            'updated_at'          => now(),
        ]);
    }

    /**
     * v3固定（冪等）
     * - すでに human_confirmed が latest なら何もしない
     */
    public function existsLatestHumanConfirmed(
        int $itemId,
        string $generatedVersion
    ): bool {
        return DB::table('item_entities')
            ->where('item_id', $itemId)
            ->where('source', 'human_confirmed')
            ->where('generated_version', $generatedVersion)
            ->where('is_latest', true)
            ->exists();
    }
}
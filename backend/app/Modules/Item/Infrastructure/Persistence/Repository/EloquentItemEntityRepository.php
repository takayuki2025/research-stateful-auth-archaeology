<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Modules\Item\Domain\Repository\ItemEntityRepository;
use Illuminate\Support\Facades\DB;

final class EloquentItemEntityRepository implements ItemEntityRepository
{
    public function markAllAsNotLatest(int $itemId): void
    {
        DB::table('item_entities')
            ->where('item_id', $itemId)
            ->where('is_latest', true)
            ->update([
                'is_latest'   => false,
                'updated_at'  => now(),
            ]);
    }

    public function create(array $attrs): int
    {
        $id = DB::table('item_entities')->insertGetId([
            'item_id'           => $attrs['item_id'],
            'brand_entity_id'   => $attrs['brand_entity_id'] ?? null,
            'condition_entity_id' => $attrs['condition_entity_id'] ?? null,
            'color_entity_id'   => $attrs['color_entity_id'] ?? null,
            'confidence'        => isset($attrs['confidence']) ? json_encode($attrs['confidence'], JSON_UNESCAPED_UNICODE) : null,
            'is_latest'         => (bool)($attrs['is_latest'] ?? true),
            'generated_version' => (string)($attrs['generated_version'] ?? 'v3'),
            'generated_at'      => $attrs['generated_at'] ?? null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return (int)$id;
    }

    public function applyAnalysisResult(int $analysisRequestId, int $actorUserId): void
    {
        $result = DB::table('analysis_results')
            ->where('analysis_request_id', $analysisRequestId)
            ->first();

        if (! $result) {
            throw new \RuntimeException('analysis_result not found');
        }

        // 1) 現在の latest を無効化
        DB::table('item_entities')
            ->where('item_id', $result->item_id)
            ->where('is_latest', true)
            ->update(['is_latest' => false]);

        // 2) 新しい entity 作成
        DB::table('item_entities')->insert([
    'item_id'             => $result->item_id,
    'brand_entity_id'     => $brandEntityId,
    'condition_entity_id' => $conditionEntityId,
    'color_entity_id'     => $colorEntityId,
    'source'              => 'ai_provisional',
    'is_latest'           => true,
    'generated_at'        => now(),
]);
    }

    public function existsLatestHumanConfirmed(int $itemId, string $version): bool
{
    return DB::table('item_entities')
        ->where('item_id', $itemId)
        ->where('source', 'human_confirmed')
        ->where('generated_version', $version)
        ->where('is_latest', true)
        ->exists();
}
}
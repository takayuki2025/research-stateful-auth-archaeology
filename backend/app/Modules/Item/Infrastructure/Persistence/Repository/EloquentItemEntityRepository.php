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
                'is_latest'  => false,
                'updated_at' => now(),
            ]);
    }

    public function create(array $attrs): int
    {
        return (int) DB::table('item_entities')->insertGetId([
            'item_id'              => $attrs['item_id'],
            'review_decision_id'   => $attrs['review_decision_id'],
            'analysis_request_id'  => $attrs['analysis_request_id'],

            'brand_entity_id'      => $attrs['brand_entity_id'] ?? null,
            'condition_entity_id'  => $attrs['condition_entity_id'] ?? null,
            'color_entity_id'      => $attrs['color_entity_id'] ?? null,

            'source'               => $attrs['source'],
            'generated_version'    => $attrs['generated_version'],

            'confidence'           => $attrs['confidence'] ?? null,

            'is_latest'            => (bool)($attrs['is_latest'] ?? true),
            'generated_at'         => $attrs['generated_at'] ?? now(),

            'created_at'           => now(),
            'updated_at'           => now(),
        ]);
    }

    public function existsByDecisionId(int $reviewDecisionId): bool
    {
        return DB::table('item_entities')
            ->where('review_decision_id', $reviewDecisionId)
            ->exists();
    }

}
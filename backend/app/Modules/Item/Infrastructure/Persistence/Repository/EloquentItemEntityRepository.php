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
}
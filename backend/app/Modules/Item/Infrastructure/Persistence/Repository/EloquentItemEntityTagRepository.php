<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Modules\Item\Domain\Repository\ItemEntityTagRepository;
use Illuminate\Support\Facades\DB;

final class EloquentItemEntityTagRepository implements ItemEntityTagRepository
{
    public function replaceTags(int $itemEntityId, string $tagType, array $tags): void
    {
        DB::table('item_entity_tags')
            ->where('item_entity_id', $itemEntityId)
            ->where('tag_type', $tagType)
            ->delete();

        foreach ($tags as $tag) {
            DB::table('item_entity_tags')->insert([
                'item_entity_id' => $itemEntityId,
                'tag_type'       => $tagType,
                'entity_id'      => $tag['entity_id'] ?? null,
                'display_name'   => $tag['display_name'],
                'confidence'     => $tag['confidence'] ?? 0.0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    public function findLatestByItemId(int $itemId): array
    {
        return DB::table('item_entity_tags as t')
            ->join('item_entities as e', 'e.id', '=', 't.item_entity_id')
            ->where('e.item_id', $itemId)
            ->where('e.is_latest', true)
            ->select('t.tag_type', 't.entity_id', 't.display_name', 't.confidence')
            ->get()
            ->groupBy('tag_type')
            ->toArray();
    }
}
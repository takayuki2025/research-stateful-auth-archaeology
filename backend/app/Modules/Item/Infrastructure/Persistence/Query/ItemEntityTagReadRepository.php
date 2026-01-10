<?php

namespace App\Modules\Item\Infrastructure\Persistence\Query;

use Illuminate\Support\Facades\DB;

final class ItemEntityTagReadRepository
{
   /**
     * 商品詳細用（v3 正式）
     */
    public function getGroupedByItemId(int $itemId): array
    {
        $rows = DB::table('item_entity_tags as t')
            ->join('item_entities as e', 'e.id', '=', 't.item_entity_id')
            ->where('e.item_id', $itemId)
            ->where('e.is_latest', true)
            ->orderBy('t.id')
            ->select(
                't.tag_type',
                't.entity_id',
                't.display_name',
                't.confidence'
            )
            ->get();

        return $rows
            ->groupBy('tag_type')
            ->map(fn ($items) => $items->map(fn ($row) => [
                'entity_id'    => $row->entity_id,
                'display_name' => $row->display_name,
                'confidence'   => $row->confidence,
            ])->values()->toArray())
            ->toArray();
    }

    /**
     * 検索用（v3 正式）
     */
    public function findItemIdsByTag(
        string $tagType,
        string $canonicalName
    ): array {
        return DB::table('item_entity_tags as t')
            ->join('item_entities as e', 'e.id', '=', 't.item_entity_id')
            ->where('t.tag_type', $tagType)
            ->where('t.display_name', $canonicalName)
            ->where('e.is_latest', true)
            ->pluck('e.item_id')
            ->unique()
            ->values()
            ->toArray();
    }
}
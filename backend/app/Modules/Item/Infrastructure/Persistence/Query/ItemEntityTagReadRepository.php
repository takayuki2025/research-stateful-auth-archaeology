<?php

namespace App\Modules\Item\Infrastructure\Persistence\Query;

use Illuminate\Support\Facades\DB;

final class ItemEntityTagReadRepository
{
    /**
     * 商品詳細用
     *
     * return example:
     * [
     *   'brand' => [
     *      ['id'=>1,'display_name'=>'Apple','confidence'=>0.9],
     *      ['id'=>2,'display_name'=>'富士フィルム','confidence'=>0.9],
     *   ],
     *   'condition' => [
     *      ['id'=>3,'display_name'=>'美品','confidence'=>1.0],
     *   ],
     *   'color' => [
     *      ['id'=>5,'display_name'=>'青','confidence'=>1.0],
     *   ],
     * ]
     */
    public function getGroupedByItemId(int $itemId): array
    {
        $rows = DB::table('item_entity_tags')
            ->where('item_id', $itemId)
            ->orderBy('id')
            ->get();

        return $rows
            ->groupBy('tag_type')
            ->map(function ($items) {
                return $items->map(fn ($row) => [
                    'entity_id'    => $row->entity_id,
                    'display_name' => $row->display_name,
                    'confidence'   => $row->confidence,
                ])->values()->toArray();
            })
            ->toArray();
    }

    /**
     * 検索用（brand / condition / color）
     */
    public function findItemIdsByTag(
        string $tagType,
        string $canonicalName
    ): array {
        return DB::table('item_entity_tags')
            ->where('tag_type', $tagType)
            ->where('display_name', $canonicalName)
            ->pluck('item_id')
            ->unique()
            ->values()
            ->toArray();
    }
}

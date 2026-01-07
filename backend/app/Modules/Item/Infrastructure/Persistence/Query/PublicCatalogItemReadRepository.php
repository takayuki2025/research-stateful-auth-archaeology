<?php

namespace App\Modules\Item\Infrastructure\Persistence\Query;

use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

final class PublicCatalogItemReadRepository
{
    /**
     * Public Catalog 用：表示判定前の生データ取得
     */
    public function paginate(
        int $limit,
        int $page,
        ?string $keyword
    ): \Illuminate\Support\Collection {
        return Item::query()
            ->from('items')
            ->leftJoin('item_entities as ie', function ($join) {
                $join->on('items.id', '=', 'ie.item_id')
                     ->where('ie.is_latest', true);
            })
            ->select([
                'items.*',
                'ie.id as entity_snapshot_id',
                'ie.brand_entity_id as brand_primary',
                'ie.condition_entity_id',
                'ie.color_entity_id',
            ])
            ->orderByDesc('items.id')
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->get();
    }
}

<?php

namespace App\Modules\Item\Infrastructure\Persistence\Query;

use App\Models\Item;

final class PublicCatalogItemReadRepository
{
    /**
     * Public Catalog 用：一覧取得（軽量）
     * 前提：確定時に item_entities の is_latest を正しく付け替える（ApplyConfirmedDecisionUseCase で保証）
     */
    public function paginate(
        int $limit,
        int $page,
        ?string $keyword
    ): \Illuminate\Support\Collection {
        $q = Item::query()
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
                'ie.source as entity_source',
            ])
            ->orderByDesc('items.id')
            ->limit($limit)
            ->offset(($page - 1) * $limit);

        if ($keyword !== null && trim($keyword) !== '') {
            $kw = '%' . trim($keyword) . '%';
            $q->where(function ($w) use ($kw) {
                $w->where('items.name', 'like', $kw)
                  ->orWhere('items.explain', 'like', $kw)
                  ->orWhere('items.brand', 'like', $kw);
            });
        }

        return $q->get();
    }
}
<?php

namespace App\Modules\Item\Infrastructure\Persistence\Query;

use App\Models\Item;
use App\Modules\Item\Infrastructure\Persistence\Query\ItemEntityTagReadRepository;
use Illuminate\Support\Facades\DB;

final class ItemReadRepository
{
    public function __construct(
        private readonly ItemEntityTagReadRepository $tagRepo
    ) {
    }


    public function findWithDisplayBrand(int $itemId)
    {
        return Item::query()
            ->leftJoin('item_entities', 'items.id', '=', 'item_entities.item_id')
            ->leftJoin(
                'brand_entities',
                'item_entities.brand_entity_id',
                '=',
                'brand_entities.id'
            )
            ->where('items.id', $itemId)
            ->select([
                'items.*',
                DB::raw('COALESCE(brand_entities.canonical_name, items.brand) as display_brand'),
            ])
            ->first();
    }

    public function paginateWithDisplayBrand(int $limit, int $page)
    {
        return Item::query()
            ->leftJoin('item_entities', function ($join) {
                $join->on('items.id', '=', 'item_entities.item_id')
                    ->where('item_entities.is_latest', true);
            })
            ->leftJoin(
                'brand_entities',
                'item_entities.brand_entity_id',
                '=',
                'brand_entities.id'
            )
            ->select([
                'items.*',
                DB::raw('COALESCE(brand_entities.canonical_name, items.brand) as display_brand'),
            ])
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * 商品詳細（entity 優先）
     */
    public function findWithDisplayEntities(int $itemId): ?array
    {
        $item = Item::query()
            ->leftJoin('item_entities as ie', function ($join) {
                $join->on('items.id', '=', 'ie.item_id')
                     ->where('ie.is_latest', true);
            })
            ->leftJoin('brand_entities as be', 'ie.brand_entity_id', '=', 'be.id')
            ->leftJoin('condition_entities as ce', 'ie.condition_entity_id', '=', 'ce.id')
            ->leftJoin('color_entities as coe', 'ie.color_entity_id', '=', 'coe.id')
            ->where('items.id', $itemId)
            ->select([
                'items.id',
                'items.shop_id',
                'items.name',
                'items.price',
                'items.explain',
                'items.remain',
                'items.item_image',

                DB::raw('be.canonical_name  as brand_primary'),
                DB::raw('ce.canonical_name  as condition_name'),
                DB::raw('coe.canonical_name as color_name'),
            ])
            ->first();

        if (! $item) {
            return null;
        }

        $tags = $this->tagRepo->getGroupedByItemId($itemId);

        $brandTags = $tags['brand'] ?? [];
        $conditionTags = $tags['condition'] ?? [];
        $colorTags = $tags['color'] ?? [];

        $categoryTags = $tags['category'] ?? [];






        return [
            'id'        => $item->id,
            'shop_id'   => $item->shop_id,
            'name'      => $item->name,
            'price'     => $item->price,
            'explain'   => $item->explain,
            'remain'    => $item->remain,

            'brands'        => array_column($brandTags, 'display_name'),
            'brand_primary' => $item->brand_primary,

            'condition' => $conditionTags[0]['display_name']
                ?? $item->condition_name
                ?? null,

            'color' => $colorTags[0]['display_name']
                ?? $item->color_name
                ?? null,

            'categories' => array_column($categoryTags, 'display_name'),

            'tags' => $tags,

            // ✅ 生パスのみ（Presentation/Resource側でURL化）
            'item_image' => $item->item_image ?: null,
        ];



    }

    /**
     * 一覧（entity 優先・軽量）
     */
    public function paginateWithDisplayEntities(
        int $limit,
        int $page
    ) {
        return Item::query()
            ->leftJoin('item_entities as ie', function ($join) {
                $join->on('items.id', '=', 'ie.item_id')
                     ->where('ie.is_latest', true);
            })
            ->leftJoin('brand_entities as be', 'ie.brand_entity_id', '=', 'be.id')
            ->leftJoin('condition_entities as ce', 'ie.condition_entity_id', '=', 'ce.id')
            ->leftJoin('color_entities as coe', 'ie.color_entity_id', '=', 'coe.id')
            ->select([
                'items.id',
                'items.name',
                'items.price',
                'items.item_image',

                DB::raw('be.canonical_name  as brand_primary'),
                DB::raw('ce.canonical_name  as condition_name'),
                DB::raw('coe.canonical_name as color_name'),
            ])
            ->paginate($limit, ['*'], 'page', $page)
            ->through(function ($row) {
                return [
                    'id'        => $row->id,
                    'name'      => $row->name,
                    'price'     => $row->price,
                    'brand'     => $row->brand_primary,
                    'condition' => $row->condition_name,
                    'color'     => $row->color_name,
                    'item_image' => $row->item_image
                        ? asset('storage/' . $row->item_image)
                        : null,
                ];
            });
    }

    private function loadTags(int $itemId): array
    {
        return DB::table('item_entity_tags')
            ->where('item_id', $itemId)
            ->select('entity_type', 'canonical_name')
            ->get()
            ->groupBy('entity_type')
            ->map(fn ($rows) => $rows->pluck('canonical_name')->values())
            ->toArray();
    }

    public function findWithDisplayEntitiesAndTags(
        int $itemId,
        ItemEntityTagReadRepository $tagRepo
    ): ?array {
        $item = $this->findWithDisplayEntities($itemId);

        if (!$item) {
            return null;
        }

        return [
            'item' => $item,
            'tags' => $tagRepo->getGroupedByItemId($itemId),
        ];
    }

    public function withFavorite(bool $isFavorited): array
    {
        return array_merge(
            $this->toArray(),
            [
                'isFavorited' => $isFavorited,
            ]
        );
    }
}

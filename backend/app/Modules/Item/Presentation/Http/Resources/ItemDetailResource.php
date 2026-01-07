<?php

namespace App\Modules\Item\Presentation\Http\Resources;

final class ItemDetailResource
{
    public static function fromReadModel(array $row): array
    {
        return [
            'id'         => $row['id'],
            'shop_id'    => $row['shop_id'],
            'name'       => $row['name'],
            'price'      => $row['price'],
            'explain'    => $row['explain'],
            'remain'     => $row['remain'],

            // brand / condition / color
            'brands'         => $row['brands'] ?? [],
            'brand_primary'  => $row['brand_primary'] ?? null,
            'condition'      => $row['condition'] ?? null,
            'color'          => $row['color'] ?? null,

            // categories
            'categories' => $row['categories'] ?? [],

            // tags（将来 UI 拡張用・そのまま返す）
            'tags' => $row['tags'] ?? [],

            // image（生パス）
            'item_image' => $row['item_image'],
        ];
    }
}
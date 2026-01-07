<?php

namespace App\Modules\Item\Presentation\Http\Resources;

final class ItemReadResource
{
    public static function fromRow(array $row): array
    {
        return [
            'id'         => $row['id'],
            'name'       => $row['name'],
            'price'      => $row['price'],

            // brands
            'brands'     => $row['brands'] ?? [],

            // condition / color
            'condition'  => $row['condition'] ?? null,
            'color'      => $row['color'] ?? null,

            'explain'    => $row['explain'] ?? null,

            // ✅ 修正点：categories を正として返す
            'categories' => $row['categories'] ?? $row['category'] ?? [],

            // ⬇ 後方互換（必要なら）
            'category'   => $row['categories'] ?? [],

            'remain'     => $row['remain'] ?? null,
            'item_image' => $row['item_image'] ?? null,
        ];
    }
}

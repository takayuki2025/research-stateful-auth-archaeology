<?php

namespace App\Modules\Item\Presentation\Http\Resources;

final class ItemDetailResource
{
    public static function fromReadModel(array $row): array
    {
        return [
            'id'        => $row['id'],
            'shop_id'   => $row['shop_id'] ?? null,
            'name'      => $row['name'],
            'price'     => $row['price'],
            'explain'   => $row['explain'],
            'remain'    => $row['remain'],
            'item_image'=> $row['item_image'],

            // ★ 主役（これが表示される）
            'display'   => $row['display'] ?? null,

            // ★ UI fallback（安全）
            'brand'     => $row['display']['brand']['name'] ?? null,
            'condition' => $row['display']['condition']['name'] ?? null,
            'color'     => $row['display']['color']['name'] ?? null,

            // 付随情報
            'comments'         => $row['comments'] ?? [],
            'is_favorited'     => $row['is_favorited'] ?? false,
            'favorites_count' => $row['favorites_count'] ?? 0,
        ];
    }
}
<?php

namespace App\Modules\Item\Domain\Factory;

use App\Models\Item as EloquentItem;
use App\Modules\Item\Domain\Entity\Item;
use App\Modules\Item\Domain\ValueObject\{
    ItemId,
    Money,
    StockCount,
    CategoryList,
    ItemImagePath,
    ItemOrigin
};

final class ItemFactory
{
    public static function fromEloquent(EloquentItem $model): Item
    {
        $categories = $model->category ?? [];

        if (is_string($categories)) {
            $decoded = json_decode($categories, true);
            $categories = is_array($decoded) ? $decoded : [];
        }

        $imagePath = null;
        if (!empty($model->item_image)) {
            $imagePath = ItemImagePath::fromRaw($model->item_image);
        }

        return Item::reconstitute(
            id: new ItemId($model->id),

            // ★ ここが今回の修正点
            itemOrigin: ItemOrigin::from($model->item_origin),
            shopId: $model->shop_id,
            createdByUserId: $model->created_by_user_id,
            name: $model->name,
            price: new Money((int) $model->price, 'JPY'),
            explain: (string) $model->explain,
            condition: (string) $model->condition,
            category: new CategoryList($categories),
            itemImage: $imagePath,
            remain: new StockCount((int) $model->remain),
        );
    }
}

<?php

namespace App\Modules\Item\Infrastructure\Mapper;

use App\Models\Item as EloquentItem;
use App\Modules\Item\Domain\Entity\Item;
use App\Modules\Item\Domain\ValueObject\ItemId;
use App\Modules\Item\Domain\ValueObject\Price;
use App\Modules\Item\Domain\ValueObject\StockCount;
use App\Modules\Item\Domain\ValueObject\CategoryList;
use App\Modules\Item\Domain\ValueObject\ItemImagePath;

class ItemMapper
{
    public function toDomain(EloquentItem $model): Item
    {
        return new Item(
            id: $model->id ? new ItemId($model->id) : null,
            userId: $model->user_id,
            shopId: $model->shop_id,
            name: $model->name,
            price: new Price($model->price),
            explain: $model->explain,
            condition: $model->condition,
            category: new CategoryList($model->category ?? []),
            // brand: $model->brand,
            itemImage: ItemImagePath::fromRaw($model->item_image),
            remain: new StockCount($model->remain),
        );
    }

    public function toEloquent(Item $item, ?EloquentItem $model = null): EloquentItem
    {
        $model ??= new EloquentItem();


        $model->user_id = null; // or 削除
        $model->shop_id = $item->getShopId();

        $model->name      = $item->getName();
        $model->price     = $item->getPrice()->getValue();
        $model->explain   = $item->getExplain();
        $model->condition = $item->getCondition();
        $model->category  = $item->getCategory()->getValues();
        // $model->brand     = $item->getBrand();
        $model->item_image = $item->getItemImage()->getPath();
        $model->remain    = $item->getRemain()->getValue();

        return $model;
    }
}

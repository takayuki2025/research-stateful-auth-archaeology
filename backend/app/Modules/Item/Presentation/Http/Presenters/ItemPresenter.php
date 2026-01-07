<?php

namespace App\Modules\Item\Presentation\Http\Presenters;

use App\Modules\Item\Domain\Entity\Item;

final class ItemPresenter
{
    public static function fromEntity(Item $item): array
    {
        return [
            'id'         => $item->getId()?->getValue(),
            'shop_id'    => $item->getShopId(),
            'name'       => $item->getName(),
            'price'      => $item->getPrice()->amount(),
            'explain'    => $item->getExplain(),
            'condition'  => $item->getCondition(),
            'category'   => $item->getCategory()->toArray(),
            'item_image' => $item->getItemImage()?->value(),
            'remain'     => $item->getRemain()->getValue(),
        ];
    }
}

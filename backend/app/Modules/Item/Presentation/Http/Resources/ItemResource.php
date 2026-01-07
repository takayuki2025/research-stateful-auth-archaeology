<?php

namespace App\Modules\Item\Presentation\Http\Resources;

use App\Modules\Item\Domain\Entity\Item;

final class ItemResource
{
    public static function fromDomain(Item $item): array
    {
        return [
            'id'        => $item->getId()?->getValue(),
            'name'      => $item->getName(),
            'price'     => $item->getPrice()->amount(),
            'explain'   => $item->getExplain(),
            'condition' => $item->getCondition(),
            'category'  => $item->getCategory()->toArray(),
            'remain'    => $item->getRemain()->getValue(),
            'user_id'   => null,

            // ★ image は public 前提
            'item_image' => $item->getItemImage()
                ? asset('storage/' . $item->getItemImage()->value())
                : null,
        ];
    }
}

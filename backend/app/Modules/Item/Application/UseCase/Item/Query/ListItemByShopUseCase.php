<?php

namespace App\Modules\Item\Application\UseCase\Item\Query;

use App\Modules\Item\Domain\Repository\ItemRepository;
use App\Modules\Item\Domain\Entity\Item;

final class ListItemByShopUseCase
{
    public function __construct(
        private ItemRepository $items
    ) {
    }

    /**
     * @return Item[]
     */
    public function execute(int $shopId): array
    {
        return $this->items->findPublicByShopId($shopId);
    }
}

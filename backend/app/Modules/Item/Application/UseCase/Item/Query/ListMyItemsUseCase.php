<?php

namespace App\Modules\Item\Application\UseCase\Item\Query;

use App\Modules\Item\Domain\Repository\ItemRepository;
use App\Modules\Item\Domain\Collection\Items;

final class ListMyItemsUseCase
{
    public function __construct(
        private readonly ItemRepository $itemRepository
    ) {
    }

    public function execute(
        int $userId,
        ?int $shopId
    ): Items {
        // shop 出品があるなら shop 基準、なければ user 基準
        if ($shopId !== null) {
            return $this->itemRepository->findByShopId($shopId);
        }

        return $this->itemRepository->findByUserId($userId);
    }
}

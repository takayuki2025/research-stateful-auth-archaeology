<?php


namespace App\Modules\Item\Application\UseCase\Cart;

use App\Modules\Item\Domain\Repository\ItemRepository;

class CartItemListUseCase
{
    public function __construct(
        private readonly ItemRepository $itemRepository,
    ) {
    }

    public function execute(int $userId): iterable
    {
        return $this->itemRepository->listByCartUser($userId);
    }
}

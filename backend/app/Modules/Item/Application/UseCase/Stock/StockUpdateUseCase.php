<?php

namespace App\Modules\Item\Application\UseCase\Stock;

use App\Modules\Item\Domain\Repository\ItemRepository;
use App\Modules\Item\Domain\Exception\InsufficientStockException;

class StockUpdateUseCase
{
    public function __construct(
        private readonly ItemRepository $itemRepository,
    ) {
    }

    public function execute(int $itemId, int $delta): void
    {
        // 現状のRepository形に合わせつつ、更新だけは lock 更新メソッドに寄せる
        $item = $this->items->findById($itemId);
        if (! $item) {
            throw new \RuntimeException('Item not found');
        }

        $current = $item->getRemain()->getValue();
        $next = $current + $delta;

        if ($next < 0) {
            throw new InsufficientStockException($itemId, abs($delta), $current);
        }

        $this->items->updateRemainWithLock($itemId, $next);
    }
}

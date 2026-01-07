<?php

namespace App\Modules\Item\Application\Service;

use App\Modules\Item\Infrastructure\Persistence\Query\ItemReadRepository;
use App\Modules\Item\Domain\Exception\ItemNotFoundException;

final class ItemDetailReadService
{
    public function __construct(
        private readonly ItemReadRepository $items
    ) {
    }

    /**
     * 商品詳細（表示用 ReadModel）
     */
    public function get(int $itemId): array
    {
        $row = $this->items->findWithDisplayEntities($itemId);

        if (!$row) {
            throw new ItemNotFoundException();
        }

        return $row;
    }
}

<?php

namespace App\Modules\Item\Application\Dto\Item;

use App\Modules\Item\Domain\Collection\Items;


final class ListItemsOutputDto
{
    public function __construct(
        public array $items,
        public int $currentPage,
        public int $total,
        public bool $hasNext,
    ) {
    }
}

<?php

namespace App\Modules\Item\Application\Dto\Item;

final class ListItemsInputDto
{
    public function __construct(
        public readonly int $limit,
        public readonly int $page,
        public readonly ?string $keyword,
        public readonly ?int $viewerUserId,
        public readonly array $viewerShopIds,
    ) {
    }
}

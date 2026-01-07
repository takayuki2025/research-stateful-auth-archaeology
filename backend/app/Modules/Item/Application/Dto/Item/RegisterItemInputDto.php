<?php

// RegisterItemInputDto.php

namespace App\Modules\Item\Application\Dto\Item;

class RegisterItemInputDto
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $shopId,
        public readonly string $name,
        public readonly int $price,
        public readonly string $explain,
        public readonly string $condition,
        public readonly array $category,
        public readonly ?string $brand,
        public readonly string $itemImagePath,
        public readonly int $remain,
    ) {
    }
}

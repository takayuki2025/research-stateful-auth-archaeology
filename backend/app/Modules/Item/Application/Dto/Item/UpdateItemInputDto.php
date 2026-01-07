<?php

// UpdateItemInputDto.php

namespace App\Modules\Item\Application\Dto\Item;


class UpdateItemInputDto
{
    public function __construct(
        public readonly int $itemId,

        // AuthContext から来る（Controllerで注入）
        public readonly int $userId,

        // ★ 出品名義
        public readonly string $itemOrigin, // USER_PERSONAL | SHOP_MANAGED

        // ★ SHOP_MANAGED のときのみ
        public readonly ?int $shopId,
        public readonly string $name,
        public readonly int $price,
        public readonly string $explain,
        public readonly string $condition,
        public readonly array $category,
        public readonly array $brandsRaw,
        public readonly ?string $itemImagePath,
        public readonly int $remain,
    ) {
    }
}

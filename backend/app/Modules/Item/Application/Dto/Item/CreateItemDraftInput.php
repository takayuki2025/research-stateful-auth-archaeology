<?php

namespace App\Modules\Item\Application\Dto\Item;

final class CreateItemDraftInput
{
    public function __construct(
        public readonly string $sellerId,
        // public readonly string $itemOrigin, // ★ 追加
        public readonly string $name,
        public readonly int $priceAmount,
        public readonly string $priceCurrency,
        public readonly ?string $brandRaw,
        public readonly ?string $explain,
        public readonly ?string $condition,
        public readonly ?array $category,
    ) {
    }
}

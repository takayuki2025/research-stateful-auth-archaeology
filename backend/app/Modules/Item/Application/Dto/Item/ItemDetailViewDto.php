<?php

namespace App\Modules\Item\Application\Dto\Item;

use App\Modules\Item\Domain\Entity\Item;

final class ItemDetailViewDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $price,
        // public readonly ?string $brand,
        public readonly string $explain,
        public readonly string $condition,
        public readonly array $category,
        public readonly ?string $item_image,
        public readonly int $remain,
        public readonly ?int $shop_id, // ★ user_id ではなく shop_id
    ) {
    }

    /**
     * Domain → View DTO
     */
    public static function fromDomain(Item $item): self
    {
        return new self(
            id: $item->getId()->getValue(),
            name: $item->getName(),
            price: $item->getPrice()->amount(),
            // brand: $item->getBrand(),
            explain: $item->getExplain(),
            condition: $item->getCondition(),
            category: $item->getCategory()->toArray(),
            item_image: $item->getItemImage()?->value(),
            remain: $item->getRemain()->getValue(),
            shop_id: $item->getShopId(), // ★ ここだけ
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}

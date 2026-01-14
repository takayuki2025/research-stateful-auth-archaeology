<?php

namespace App\Modules\Item\ReadModel\PublicCatalog;

final class PublicCatalogItemDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $price,
        public readonly ?string $brandPrimary,
        public readonly ?string $conditionName,
        public readonly ?string $colorName,
        public readonly ?string $itemImagePath,
        public readonly \DateTimeInterface $publishedAt,
        public readonly ?string $itemOrigin,
        public readonly ?string $displayType, // 'STAR' | 'COMET' | null
        public readonly ?array $display,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'brandPrimary' => $this->brandPrimary,
            'conditionName' => $this->conditionName,
            'colorName' => $this->colorName,
            'itemImagePath' => $this->itemImagePath,
            'publishedAt' => $this->publishedAt->format('Y-m-d H:i:s'),
            'item_origin' => $this->itemOrigin,
            'displayType' => $this->displayType,
        ];
    }
}

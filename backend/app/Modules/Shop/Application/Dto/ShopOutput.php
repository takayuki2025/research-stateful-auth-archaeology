<?php

namespace App\Modules\Shop\Application\Dto;

use App\Modules\Shop\Domain\Entity\Shop;


final class ShopOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $shopCode,   // ★ 追加
        public readonly string $name,
        public readonly string $status,
    ) {
    }

    public static function fromEntity(Shop $shop): self
    {
        return new self(
            id: $shop->id(),
            shopCode: $shop->shopCode(),    // ★
            name: $shop->name(),
            status: $shop->status()->value,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'shop_code' => $this->shopCode, // ★
            'name' => $this->name,
            'status' => $this->status,
        ];
    }
}

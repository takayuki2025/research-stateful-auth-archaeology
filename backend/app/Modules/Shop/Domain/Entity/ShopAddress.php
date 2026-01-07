<?php

namespace App\Modules\Shop\Domain\Entity;

use App\Modules\Order\Domain\ValueObject\Address;

final class ShopAddress
{
    public function __construct(
        private ?int $id,
        private int $shopId,
        private Address $address,
        private bool $isDefault,
    ) {
    }

    /* =========================
       Factory
    ========================= */

    public static function createDefault(
        int $shopId,
        Address $address
    ): self {
        return new self(
            id: null,
            shopId: $shopId,
            address: $address,
            isDefault: true,
        );
    }

    public static function reconstitute(
        int $id,
        int $shopId,
        Address $address,
        bool $isDefault
    ): self {
        return new self(
            id: $id,
            shopId: $shopId,
            address: $address,
            isDefault: $isDefault,
        );
    }

    /* =========================
       Getters
    ========================= */

    public function id(): ?int
    {
        return $this->id;
    }

    public function shopId(): int
    {
        return $this->shopId;
    }

    public function address(): Address
    {
        return $this->address;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }
}

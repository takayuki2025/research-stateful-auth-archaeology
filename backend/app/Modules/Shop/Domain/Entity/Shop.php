<?php

namespace App\Modules\Shop\Domain\Entity;

use App\Modules\Shop\Domain\Enum\ShopStatus;
use App\Modules\Order\Domain\ValueObject\Address; // ★暫定: 後で Common に移す

final class Shop
{
    private ?Address $shippingAddress;

    public function __construct(
        private ?int $id,
        private string $shopCode,
        private int $ownerUserId,
        private string $name,
        private ShopStatus $status,
        ?Address $shippingAddress = null, // ★追加
    ) {
        $this->shippingAddress = $shippingAddress;
    }

    public static function create(
        int $ownerUserId,
        string $name,
        string $shopCode,
    ): self {
        return new self(
            id: null,
            shopCode: $shopCode,
            ownerUserId: $ownerUserId,
            name: $name,
            status: ShopStatus::ACTIVE,
            shippingAddress: null,
        );
    }

    public function withShippingAddress(Address $address): self
    {
        $clone = clone $this;
        $clone->shippingAddress = $address;
        return $clone;
    }

    public function shippingAddress(): Address
    {
        if (! $this->shippingAddress) {
            throw new \DomainException('Shop shipping address not set');
        }
        return $this->shippingAddress;
    }

    // 既存 getter はそのまま
    public function id(): ?int
    {
        return $this->id;
    }
    public function shopCode(): string
    {
        return $this->shopCode;
    }
    public function ownerUserId(): int
    {
        return $this->ownerUserId;
    }
    public function name(): string
    {
        return $this->name;
    }
    public function status(): ShopStatus
    {
        return $this->status;
    }
    public function isActive(): bool
    {
        return $this->status === ShopStatus::ACTIVE;
    }
    public function suspend(): void
    {
        $this->status = ShopStatus::SUSPENDED;
    }
}

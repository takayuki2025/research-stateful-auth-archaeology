<?php

namespace App\Modules\Shop\Application\Dto;

use App\Modules\Shop\Domain\Enum\ShopStatus;

final class ShopContext
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        public readonly int $shopId,
        public readonly string $shopCode,
        public readonly ShopStatus $shopStatus,
        public readonly ?int $ownerUserId,
        public readonly array $roles,
    ) {
    }

    public function isActive(): bool
    {
        return $this->shopStatus === ShopStatus::ACTIVE;
    }

    public function isOwner(int $userId): bool
    {
        return $this->ownerUserId !== null && $this->ownerUserId === $userId;
    }

    public function hasRole(string $slug): bool
    {
        return in_array($slug, $this->roles, true);
    }

    /**
     * 典型権限（ShopRolePolicy と合わせて使う想定）
     */
    public function canManageShop(): bool
    {
        return $this->hasRole('owner') || $this->hasRole('manager');
    }

    public function canManageOrders(): bool
    {
        return $this->hasRole('owner') || $this->hasRole('manager') || $this->hasRole('staff');
    }

    public function canManageShipments(): bool
    {
        return $this->canManageOrders();
    }

    public function canRefund(): bool
    {
        return $this->hasRole('owner') || $this->hasRole('manager');
    }
}

<?php

namespace App\Modules\Shop\Domain\Repository;

use App\Modules\Shop\Domain\Entity\Shop;
use App\Modules\Order\Domain\ValueObject\Address;

interface ShopRepository
{
    public function save(Shop $shop): Shop;

    public function findByOwnerUserId(int $userId): ?Shop;

    public function findById(int $shopId): ?Shop;

    public function findByShopCode(string $shopCode): ?Shop;

    public function ensureDefaultShippingAddress(
        int $shopId,
        Address $address
    ): void;
}

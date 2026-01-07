<?php

namespace App\Modules\Shop\Domain\Repository;

use App\Modules\Shop\Domain\Entity\ShopAddress;

interface ShopAddressRepository
{
    public function findDefaultByShopId(int $shopId): ?ShopAddress;

    public function save(ShopAddress $address): ShopAddress;
}

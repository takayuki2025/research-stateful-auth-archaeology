<?php

namespace App\Modules\Shop\Domain\Repository;

use App\Modules\Shop\Domain\Entity\Shop;

interface ShopQueryRepository
{
    public function findByCode(string $shopCode): ?Shop;
}

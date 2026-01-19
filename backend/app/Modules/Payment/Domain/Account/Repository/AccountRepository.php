<?php

namespace App\Modules\Payment\Domain\Account\Repository;

interface AccountRepository
{
    public function findOrCreateForShop(int $shopId, string $currency): int; // returns account_id
}
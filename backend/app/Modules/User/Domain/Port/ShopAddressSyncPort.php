<?php

namespace App\Modules\User\Domain\Port;

interface ShopAddressSyncPort
{
    public function syncFromUserProfile(int $userId): void;
}

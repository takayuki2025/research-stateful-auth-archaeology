<?php

namespace App\Modules\User\Infrastructure\External;

use App\Modules\User\Domain\Port\ShopAddressSyncPort;

final class NullShopAddressSyncAdapter implements ShopAddressSyncPort
{
    public function syncFromUserProfile(int $userId): void
    {
        // no-op
    }
}

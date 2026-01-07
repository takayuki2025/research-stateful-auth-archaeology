<?php

namespace App\Modules\User\Infrastructure\Adapter;

use App\Modules\User\Domain\Port\ShopAddressSyncPort;
use App\Modules\Shop\Application\UseCase\EnsureShopAddressFromProfileUseCase;

final class ShopAddressSyncAdapter implements ShopAddressSyncPort
{
    public function __construct(
        private EnsureShopAddressFromProfileUseCase $ensure
    ) {
    }

    public function syncFromUserProfile(int $userId): void
    {
        $this->ensure->handle($userId);
    }
}

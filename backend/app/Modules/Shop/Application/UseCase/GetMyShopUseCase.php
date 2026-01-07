<?php

namespace App\Modules\Shop\Application\UseCase;

use App\Modules\Shop\Application\Dto\ShopOutput;
use App\Modules\Shop\Domain\Repository\ShopRepository;

final class GetMyShopUseCase
{
    public function __construct(
        private ShopRepository $shops,
    ) {
    }

    public function handle(int $userId): ?Shop
    {
        return $this->shops->findByOwnerUserId($userId);
    }
}

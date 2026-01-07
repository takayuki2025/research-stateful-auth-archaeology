<?php

namespace App\Modules\Shop\Infrastructure\Listener;

use App\Modules\Order\Domain\Event\OrderPaid;
use App\Modules\Shop\Application\UseCase\EnsureShopAddressFromProfileUseCase;
use App\Modules\Shop\Domain\Repository\ShopRepository;

final class EnsureShopAddressOnOrderPaid
{
    public function __construct(
        private ShopRepository $shops,
        private EnsureShopAddressFromProfileUseCase $useCase,
    ) {
    }

    public function handle(OrderPaid $event): void
    {
        // OrderPaid → shopId → ownerUserId
        $shop = $this->shops->findById($event->shopId);
        if (! $shop) {
            return;
        }

        $this->useCase->handle($shop->ownerUserId());
    }
}

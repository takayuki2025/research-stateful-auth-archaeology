<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Shop\Domain\Repository\ShopRepository;
use App\Modules\Shop\Domain\Repository\ShopLedgerRepository;
use App\Modules\Shop\Infrastructure\Persistence\EloquentShopRepository;
use App\Modules\Shop\Infrastructure\Persistence\EloquentShopLedgerRepository;

final class ShopModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Shop aggregate
        $this->app->bind(
            ShopRepository::class,
            EloquentShopRepository::class
        );

        // Shop Ledger (Accounting)
        $this->app->bind(
            ShopLedgerRepository::class,
            EloquentShopLedgerRepository::class
        );
    }
}

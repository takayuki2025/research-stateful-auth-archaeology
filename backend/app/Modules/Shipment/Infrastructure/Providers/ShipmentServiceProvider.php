<?php

namespace App\Modules\Shipment\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event; // 💡 必須：これを使って配線します
use App\Modules\Shipment\Domain\Repository\ShipmentRepository;
use App\Modules\Shipment\Domain\Repository\ShipmentEventRepository;
use App\Modules\Shipment\Infrastructure\Persistence\EloquentShipmentRepository;
use App\Modules\Shipment\Infrastructure\Persistence\EloquentShipmentEventRepository;
use App\Modules\Shipment\Domain\Service\EtaCalculator;
use App\Modules\Shipment\Domain\Repository\ShipmentQueryRepository;
use App\Modules\Shipment\Infrastructure\Persistence\EloquentShipmentQueryRepository;
use App\Modules\Shipment\Domain\Repository\ShipmentEventReadRepository;
use App\Modules\Shipment\Infrastructure\Persistence\Query\DbShipmentEventReadRepository;

// 💡 イベントとリスナーをインポート

final class ShipmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(
            ShipmentRepository::class,
            EloquentShipmentRepository::class
        );

        $this->app->bind(
            ShipmentEventRepository::class,
            EloquentShipmentEventRepository::class
        );


        $this->app->bind(
            ShipmentQueryRepository::class,
            EloquentShipmentQueryRepository::class
        );


        // Domain Services
        $this->app->singleton(EtaCalculator::class);


        $this->app->bind(
            ShipmentEventReadRepository::class,
            DbShipmentEventReadRepository::class
        );

    }

    public function boot(): void
    {

    }
}

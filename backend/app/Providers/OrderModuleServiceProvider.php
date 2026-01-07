<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Order\Domain\Repository\OrderRepository;
use App\Modules\Order\Domain\Repository\OrderHistoryRepository;
use App\Modules\Order\Infrastructure\Persistence\EloquentOrderRepository;
use App\Modules\Order\Infrastructure\Persistence\EloquentOrderHistoryRepository;

final class OrderModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
        $this->app->bind(OrderHistoryRepository::class, EloquentOrderHistoryRepository::class);
    }
}

<?php

namespace App\Modules\ProviderIntel\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\ProviderIntel\Domain\Repository\CatalogSourceRepository;
use App\Modules\ProviderIntel\Infrastructure\Persistence\Repository\EloquentCatalogSourceRepository;

final class ProviderIntelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CatalogSourceRepository::class, EloquentCatalogSourceRepository::class);
    }
}
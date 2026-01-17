<?php

declare(strict_types=1);

namespace App\Modules\AtlasKernel\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

use App\Modules\AtlasKernel\Domain\Repository\DecisionLedgerRepository;
use App\Modules\AtlasKernel\Infrastructure\Persistence\Repository\EloquentDecisionLedgerRepository;

final class AtlasServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            DecisionLedgerRepository::class,
            EloquentDecisionLedgerRepository::class
        );
    }
}
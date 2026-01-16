<?php

namespace App\Modules\Item\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

// Domain
use App\Modules\Item\Domain\Repository\{
    AnalysisRequestRepository,
    AnalysisResultRepository,
    BrandEntityRepository,
    ColorEntityRepository,
    ConditionEntityRepository,
};

// Infra
use App\Modules\Item\Infrastructure\Persistence\Repository\{
    EloquentAnalysisRequestRepository,
    EloquentAnalysisResultRepository,
    EloquentBrandEntityRepository,
    EloquentColorEntityRepository,
    EloquentConditionEntityRepository,
};

final class ItemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // analysis
        $this->app->bind(
            AnalysisRequestRepository::class,
            EloquentAnalysisRequestRepository::class
        );

        $this->app->bind(
            AnalysisResultRepository::class,
            EloquentAnalysisResultRepository::class
        );

        // 🔥 Resolve v3 固定
        $this->app->bind(
            BrandEntityRepository::class,
            EloquentBrandEntityRepository::class
        );

        $this->app->bind(
            ColorEntityRepository::class,
            EloquentColorEntityRepository::class
        );

        $this->app->bind(
            ConditionEntityRepository::class,
            EloquentConditionEntityRepository::class
        );
    }
}

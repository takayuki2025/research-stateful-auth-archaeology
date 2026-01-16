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

use App\Modules\Item\Domain\Repository\CanonicalEntityRepository;
use App\Modules\Item\Infrastructure\Persistence\Repository\EloquentCanonicalEntityRepository;
use App\Modules\Item\Domain\Repository\LearningCandidateRepository;
use App\Modules\Item\Infrastructure\Persistence\Repository\EloquentLearningCandidateRepository;
use App\Modules\Item\Domain\Repository\BrandEntityQueryRepository;
use App\Modules\Item\Infrastructure\Persistence\Repository\EloquentBrandEntityQueryRepository;
use App\Modules\Item\Domain\Repository\ColorEntityQueryRepository;
use App\Modules\Item\Infrastructure\Persistence\Repository\EloquentColorEntityQueryRepository;
use App\Modules\Item\Domain\Repository\ConditionEntityQueryRepository;
use App\Modules\Item\Infrastructure\Persistence\Repository\EloquentConditionEntityQueryRepository;




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

        $this->app->bind(
    CanonicalEntityRepository::class,
    EloquentCanonicalEntityRepository::class
);

$this->app->bind(
            LearningCandidateRepository::class,
            EloquentLearningCandidateRepository::class
        );

        $this->app->bind(
    BrandEntityQueryRepository::class,
    EloquentBrandEntityQueryRepository::class
);

$this->app->bind(
    ColorEntityQueryRepository::class,
    EloquentColorEntityQueryRepository::class
);

$this->app->bind(
    ConditionEntityQueryRepository::class,
    EloquentConditionEntityQueryRepository::class
);
    }
}

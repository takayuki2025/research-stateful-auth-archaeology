<?php

namespace App\Modules\Item\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use App\Modules\Item\Infrastructure\Persistence\Repository\EloquentAnalysisRequestRepository;
use App\Modules\Item\Infrastructure\Persistence\Repository\EloquentAnalysisResultRepository;

final class ItemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AnalysisRequestRepository::class, EloquentAnalysisRequestRepository::class);

        $this->app->bind(
            AnalysisResultRepository::class,
            EloquentAnalysisResultRepository::class
        );
    }
}
<?php

namespace App\Modules\Review\Infrastructure;

use App\Modules\Review\Domain\Repository\ReviewDecisionRepository;
use App\Modules\Review\Domain\Repository\ReviewQueryRepository;
use App\Modules\Review\Infrastructure\Persistence\EloquentReviewDecisionRepository;
use App\Modules\Review\Infrastructure\Persistence\EloquentReviewQueryRepository;
use App\Modules\Review\Domain\Repository\ReviewQueueRepository;
use App\Modules\Review\Infrastructure\Persistence\EloquentReviewQueueRepository;
use Illuminate\Support\ServiceProvider;

final class ReviewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ReviewDecisionRepository::class, EloquentReviewDecisionRepository::class);
        $this->app->bind(ReviewQueryRepository::class, EloquentReviewQueryRepository::class);
        $this->app->bind(ReviewQueueRepository::class, EloquentReviewQueueRepository::class);
    }
}
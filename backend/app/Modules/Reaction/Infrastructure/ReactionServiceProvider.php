<?php

namespace App\Modules\Reaction\Infrastructure;

use Illuminate\Support\ServiceProvider;
use App\Modules\Reaction\Domain\Repository\FavoriteRepository;
use App\Modules\Reaction\Infrastructure\Persistence\EloquentFavoriteRepository;

final class ReactionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FavoriteRepository::class, EloquentFavoriteRepository::class);
    }
}

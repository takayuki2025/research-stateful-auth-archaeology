<?php

namespace App\Modules\Search\Presentation\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Search\Domain\Repository\ItemSearchRepository;
use App\Modules\Search\Infrastructure\Persistence\Repository\EloquentItemSearchRepository;

final class SearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ItemSearchRepository::class, EloquentItemSearchRepository::class);
    }
}

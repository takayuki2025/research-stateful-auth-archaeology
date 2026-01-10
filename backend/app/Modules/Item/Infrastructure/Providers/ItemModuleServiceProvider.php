<?php

namespace App\Modules\Item\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Item\Domain\Repository\ItemRepository;
use App\Modules\Item\Domain\Repository\ItemDraftRepository;
use App\Modules\Item\Infrastructure\Persistence\Repository\EloquentItemRepository;
use App\Modules\Item\Infrastructure\Persistence\Repository\EloquentItemDraftRepository;
use App\Modules\Item\Application\Query\PublicCatalogQueryService;
use App\Modules\Item\Infrastructure\Persistence\Query\EloquentPublicCatalogQueryService;
use App\Modules\Item\Application\Port\FavoriteItemReadPort;
use App\Modules\Item\Infrastructure\Persistence\Query\EloquentFavoriteItemReadAdapter;
use App\Modules\Item\Domain\Port\BrandNormalizationPort;
use App\Modules\Item\Infrastructure\External\AtlaskernelBrandNormalizer;
use App\Modules\Item\Domain\Repository\ItemEntityRepository;
use App\Modules\Item\Domain\Repository\ItemEntityTagRepository;
use App\Modules\Item\Infrastructure\Persistence\Repository\EloquentItemEntityRepository;
use App\Modules\Item\Infrastructure\Persistence\Repository\EloquentItemEntityTagRepository;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use App\Modules\Item\Infrastructure\Persistence\Repository\EloquentAnalysisResultRepository;

final class ItemModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ItemRepository::class, EloquentItemRepository::class);
        $this->app->bind(ItemDraftRepository::class, EloquentItemDraftRepository::class);

        $this->app->bind(PublicCatalogQueryService::class, EloquentPublicCatalogQueryService::class);
        $this->app->bind(FavoriteItemReadPort::class, EloquentFavoriteItemReadAdapter::class);

        $this->app->bind(BrandNormalizationPort::class, AtlaskernelBrandNormalizer::class);

        $this->app->bind(ItemEntityRepository::class, EloquentItemEntityRepository::class);
        $this->app->bind(ItemEntityTagRepository::class, EloquentItemEntityTagRepository::class);

        $this->app->bind(AnalysisResultRepository::class,EloquentAnalysisResultRepository::class);
    }
}

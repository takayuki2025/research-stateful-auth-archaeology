<?php

namespace App\Modules\ProviderIntel\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\ProviderIntel\Domain\Repository\CatalogSourceRepository;
use App\Modules\ProviderIntel\Infrastructure\Persistence\Repository\EloquentCatalogSourceRepository;
use App\Modules\ProviderIntel\Domain\Repository\ExtractedDocumentRepository;
use App\Modules\ProviderIntel\Infrastructure\Persistence\Repository\EloquentExtractedDocumentRepository;
use App\Modules\ProviderIntel\Domain\Repository\DocumentDiffRepository;
use App\Modules\ProviderIntel\Infrastructure\Persistence\Repository\EloquentDocumentDiffRepository;

final class ProviderIntelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CatalogSourceRepository::class, EloquentCatalogSourceRepository::class);
        $this->app->bind(ExtractedDocumentRepository::class, EloquentExtractedDocumentRepository::class);
        $this->app->bind(DocumentDiffRepository::class, EloquentDocumentDiffRepository::class);
    }
}
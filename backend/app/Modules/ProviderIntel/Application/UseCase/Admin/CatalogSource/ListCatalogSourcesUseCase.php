<?php

namespace App\Modules\ProviderIntel\Application\UseCase\Admin\CatalogSource;

use App\Modules\ProviderIntel\Application\Dto\CatalogSourceDto;
use App\Modules\ProviderIntel\Domain\Repository\CatalogSourceRepository;

final class ListCatalogSourcesUseCase
{
    public function __construct(
        private CatalogSourceRepository $sources,
    ) {
    }

    public function handle(?int $providerId, ?string $status, int $limit, int $offset): array
    {
        $items = $this->sources->list($providerId, $status, $limit, $offset);

        return array_map(
            fn($e) => CatalogSourceDto::fromEntity($e)->toArray(),
            $items
        );
    }
}
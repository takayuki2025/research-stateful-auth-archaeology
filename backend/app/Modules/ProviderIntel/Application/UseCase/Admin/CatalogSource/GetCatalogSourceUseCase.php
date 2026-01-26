<?php

namespace App\Modules\ProviderIntel\Application\UseCase\Admin\CatalogSource;

use App\Modules\ProviderIntel\Application\Dto\CatalogSourceDto;
use App\Modules\ProviderIntel\Domain\Repository\CatalogSourceRepository;

final class GetCatalogSourceUseCase
{
    public function __construct(
        private CatalogSourceRepository $sources,
    ) {
    }

    public function handle(int $sourceId): array
    {
        $e = $this->sources->find($sourceId);
        if (!$e) {
            throw new \DomainException('CatalogSource not found');
        }
        return CatalogSourceDto::fromEntity($e)->toArray();
    }
}

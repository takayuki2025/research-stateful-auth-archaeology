<?php

namespace App\Modules\ProviderIntel\Application\UseCase\Admin\CatalogSource;

use App\Modules\ProviderIntel\Application\Service\CatalogSourceUrlHasher;
use App\Modules\ProviderIntel\Domain\Entity\CatalogSource;
use App\Modules\ProviderIntel\Domain\Repository\CatalogSourceRepository;

final class UpsertCatalogSourceUseCase
{
    public function __construct(
        private CatalogSourceRepository $sources,
        private CatalogSourceUrlHasher $hasher,
    ) {
    }

    /**
     * Returns persisted id.
     */
    public function handle(
        ?int $id,
        ?int $projectId,
        int $providerId,
        string $sourceType,
        ?string $sourceUrl,
        string $updateFrequency,
        string $status,
        ?string $notes,
    ): int {
        $sourceUrlHash = $sourceUrl ? $this->hasher->hash($sourceUrl) : hash('sha256', '');

        $entity = new CatalogSource(
            id: $id,
            projectId: $projectId,
            providerId: $providerId,
            sourceType: $sourceType,
            sourceUrl: $sourceUrl,
            sourceUrlHash: $sourceUrlHash,
            updateFrequency: $updateFrequency,
            status: $status,
            lastFetchedAt: null,
            lastHash: null,
            notes: $notes,
        );

        return $this->sources->upsert($entity);
    }
}
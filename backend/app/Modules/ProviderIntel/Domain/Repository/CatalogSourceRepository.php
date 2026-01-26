<?php

namespace App\Modules\ProviderIntel\Domain\Repository;

use App\Modules\ProviderIntel\Domain\Entity\CatalogSource;

interface CatalogSourceRepository
{
    /** @return array<int, CatalogSource> */
    public function list(?int $providerId, ?string $status, int $limit, int $offset): array;

    public function find(int $id): ?CatalogSource;

    /**
     * Upsert by (provider_id, source_url_hash).
     * Returns persisted id.
     */
    public function upsert(CatalogSource $source): int;

    /**
     * Persist last_hash/last_fetched_at update.
     */
    public function updateLastFetch(CatalogSource $source): void;
}
<?php

namespace App\Modules\ProviderIntel\Application\Dto;

use App\Modules\ProviderIntel\Domain\Entity\CatalogSource;

final class CatalogSourceDto
{
    public function __construct(
        public int $id,
        public ?int $project_id,
        public int $provider_id,
        public string $source_type,
        public ?string $source_url,
        public string $source_url_hash,
        public string $update_frequency,
        public string $status,
        public ?string $last_hash,
        public ?string $last_fetched_at,
        public ?string $notes,
    ) {
    }

    public static function fromEntity(CatalogSource $e): self
    {
        return new self(
            id: (int)$e->id(),
            project_id: $e->projectId(),
            provider_id: $e->providerId(),
            source_type: $e->sourceType(),
            source_url: $e->sourceUrl(),
            source_url_hash: $e->sourceUrlHash(),
            update_frequency: $e->updateFrequency(),
            status: $e->status(),
            last_hash: $e->lastHash(),
            last_fetched_at: $e->lastFetchedAt()?->format('c'),
            notes: $e->notes(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'provider_id' => $this->provider_id,
            'source_type' => $this->source_type,
            'source_url' => $this->source_url,
            'source_url_hash' => $this->source_url_hash,
            'update_frequency' => $this->update_frequency,
            'status' => $this->status,
            'last_hash' => $this->last_hash,
            'last_fetched_at' => $this->last_fetched_at,
            'notes' => $this->notes,
        ];
    }
}
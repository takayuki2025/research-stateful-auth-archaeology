<?php

namespace App\Modules\ProviderIntel\Domain\Entity;

final class CatalogSource
{
    public function __construct(
        private ?int $id,
        private ?int $projectId,
        private int $providerId,
        private string $sourceType,
        private ?string $sourceUrl,
        private string $sourceUrlHash,
        private string $updateFrequency,
        private string $status,
        private ?\DateTimeImmutable $lastFetchedAt,
        private ?string $lastHash,
        private ?string $notes,
    ) {
    }

    public function id(): ?int { return $this->id; }
    public function projectId(): ?int { return $this->projectId; }
    public function providerId(): int { return $this->providerId; }
    public function sourceType(): string { return $this->sourceType; }
    public function sourceUrl(): ?string { return $this->sourceUrl; }
    public function sourceUrlHash(): string { return $this->sourceUrlHash; }
    public function updateFrequency(): string { return $this->updateFrequency; }
    public function status(): string { return $this->status; }
    public function lastFetchedAt(): ?\DateTimeImmutable { return $this->lastFetchedAt; }
    public function lastHash(): ?string { return $this->lastHash; }
    public function notes(): ?string { return $this->notes; }

    public function withLastFetch(string $newHash, \DateTimeImmutable $fetchedAt): self
    {
        return new self(
            $this->id,
            $this->projectId,
            $this->providerId,
            $this->sourceType,
            $this->sourceUrl,
            $this->sourceUrlHash,
            $this->updateFrequency,
            $this->status,
            $fetchedAt,
            $newHash,
            $this->notes,
        );
    }
}
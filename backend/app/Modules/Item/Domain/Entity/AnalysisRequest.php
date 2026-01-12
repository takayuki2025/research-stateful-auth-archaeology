<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Entity;

use App\Models\AnalysisRequest as EloquentAnalysisRequest;

final class AnalysisRequest
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $tenantId,
        public readonly int $itemId,
        public readonly string $analysisVersion,
        public readonly string $status,
        public readonly int $retryCount,
        public readonly ?\DateTimeImmutable $startedAt,
        public readonly ?\DateTimeImmutable $finishedAt,
        public readonly \DateTimeImmutable $createdAt,
    ) {}

    public static function fromEloquent(EloquentAnalysisRequest $row): self
    {
        return new self(
            id: $row->id,
            tenantId: $row->tenant_id,
            itemId: $row->item_id,
            analysisVersion: $row->analysis_version,
            status: $row->status,
            retryCount: (int) $row->retry_count,
            startedAt: $row->started_at?->toDateTimeImmutable(),
            finishedAt: $row->finished_at?->toDateTimeImmutable(),
            createdAt: $row->created_at->toDateTimeImmutable(),
        );
    }
}
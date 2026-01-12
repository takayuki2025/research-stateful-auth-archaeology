<?php

namespace App\Modules\Item\Domain\ValueObject;

final class AnalysisRequestRecord
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $tenantId,
        public readonly int $itemId,
        public readonly string $analysisVersion,
        public readonly string $payloadHash,
        public readonly string $idempotencyKey,
        public readonly string $status, // pending|running|done|failed
        public readonly int $retryCount,
    ) {}

    public function isDone(): bool
    {
        return $this->status === 'done';
    }
}

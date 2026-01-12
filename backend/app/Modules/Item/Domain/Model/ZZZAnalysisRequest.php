<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Model;

use Carbon\CarbonImmutable;

final class AnalysisRequest
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $tenantId,
        public readonly int $itemId,
        public readonly string $analysisVersion,
        public readonly string $payloadHash,
        public readonly string $idempotencyKey,
        public readonly string $status,
        public readonly ?CarbonImmutable $startedAt,
        public readonly ?CarbonImmutable $finishedAt,
        public readonly ?int $originalRequestId,
        public readonly int $retryCount,
        public readonly ?string $triggeredByType,
        public readonly ?int $triggeredBy,
        public readonly ?string $triggerReason,
        public readonly ?string $requestedVersion,
        public readonly ?int $replayIndex,
        public readonly CarbonImmutable $createdAt,
        public readonly CarbonImmutable $updatedAt,
    ) {}

    /**
     * Replay 用 Factory
     */
    public static function replayFrom(
        self $original,
        string $version,
        int $actorUserId,
        ?string $triggerReason = null,
    ): self {
        return new self(
            id: null,
            tenantId: $original->tenantId,
            itemId: $original->itemId,
            analysisVersion: $version,
            payloadHash: $original->payloadHash,
            idempotencyKey: self::generateIdempotencyKey($original->id, $version),
            status: 'pending',
            startedAt: null,
            finishedAt: null,
            originalRequestId: $original->id,
            retryCount: $original->retryCount + 1,
            triggeredByType: 'human',
            triggeredBy: $actorUserId,
            triggerReason: $triggerReason,
            requestedVersion: $version,
            replayIndex: $original->retryCount + 1,
            createdAt: CarbonImmutable::now(),
            updatedAt: CarbonImmutable::now(),
        );
    }

    private static function generateIdempotencyKey(int $originalId, string $version): string
    {
        return hash(
            'sha256',
            sprintf('replay:%d:%s:%s', $originalId, $version, microtime(true))
        );
    }
}
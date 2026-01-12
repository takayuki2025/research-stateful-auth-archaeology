<?php

namespace App\Modules\Item\Domain\Repository;

use App\Modules\Item\Domain\ValueObject\AnalysisRequestRecord;
use App\Modules\Item\Domain\Entity\AnalysisRequest;

interface AnalysisRequestRepository
{
    /**
     * Reserve request by idempotency_key (insert if not exists, never update existing).
     * Returns the current record (new or existing).
     */
    public function reserveOrGet(
        ?int $tenantId,
        int $itemId,
        string $analysisVersion,
        string $payloadHash,
        string $idempotencyKey
    ): AnalysisRequestRecord;

    /**
     * Compare-and-swap transition to running.
     * Returns true if acquired; false if someone else already running/done.
     */
    public function markRunning(int $requestId): bool;

    public function markDone(int $requestId): void;

    public function markFailed(int $requestId, string $errorCode, string $errorMessage): void;

    public function appendEvent(int $requestId, string $eventType, array $payload = []): void;

    public function listByShopCode(string $shopCode): array;

    public function findOrFail(int $id): AnalysisRequest;
}
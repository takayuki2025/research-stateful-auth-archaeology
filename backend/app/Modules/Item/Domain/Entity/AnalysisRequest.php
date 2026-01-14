<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Entity;

use App\Models\AnalysisRequest as EloquentAnalysisRequest;
use DateTimeImmutable;

final class AnalysisRequest
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $tenantId,
        public readonly int $itemId,

        // ✅ v3：nullable で正式復活
        public readonly ?string $itemDraftId,

        public readonly string $analysisVersion,
        public readonly ?string $requestedVersion,
        public readonly string $payloadHash,
        public readonly string $idempotencyKey,
        public readonly string $status,
        public readonly int $retryCount,
        public readonly ?int $originalRequestId,
        public readonly ?int $replayIndex,
        public readonly string $triggeredByType,
        public readonly ?int $triggeredBy,
        public readonly ?string $triggerReason,
        public readonly ?DateTimeImmutable $startedAt,
        public readonly ?DateTimeImmutable $finishedAt,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
    ) {}

    public static function fromEloquent(EloquentAnalysisRequest $row): self
{
    return new self(
        id: (int)$row->id,
        tenantId: $row->tenant_id !== null ? (int)$row->tenant_id : null,
        itemId: (int)$row->item_id,

        // ✅ nullable
        itemDraftId: $row->item_draft_id !== null ? (string)$row->item_draft_id : null,

        analysisVersion: (string)$row->analysis_version,
        requestedVersion: $row->requested_version !== null ? (string)$row->requested_version : null,
        payloadHash: (string)$row->payload_hash,
        idempotencyKey: (string)$row->idempotency_key,
        status: (string)$row->status,
        retryCount: (int)$row->retry_count,
        originalRequestId: $row->original_request_id !== null ? (int)$row->original_request_id : null,
        replayIndex: $row->replay_index !== null ? (int)$row->replay_index : null,
        triggeredByType: (string)$row->triggered_by_type,
        triggeredBy: $row->triggered_by !== null ? (int)$row->triggered_by : null,
        triggerReason: $row->trigger_reason !== null ? (string)$row->trigger_reason : null,
        startedAt: $row->started_at?->toDateTimeImmutable(),
        finishedAt: $row->finished_at?->toDateTimeImmutable(),
        createdAt: $row->created_at->toDateTimeImmutable(),
        updatedAt: $row->updated_at->toDateTimeImmutable(),
    );
}

    /**
     * Replay 用の新規Entity（idはDB採番前なので0）
     */
    public static function replayFrom(
        self $original,
        string $requestedVersion,
        int $replayIndex,
        int $actorUserId,
        string $triggerReason,
    ): self {
        $now = new DateTimeImmutable();

        // idempotency は「元ID + version + replayIndex」で衝突しないようにする
        $idempotencyKey = sprintf('replay:%d:%s:%d', $original->id, $requestedVersion, $replayIndex);
        $payloadHash = $original->payloadHash; // まずは同payload扱いでOK（将来差分payloadに対応）

        return new self(
            id: 0,
            tenantId: $original->tenantId,
            itemId: $original->itemId,
            analysisVersion: $requestedVersion,       // 実行するversion
            requestedVersion: $requestedVersion,      // 監査用
            payloadHash: $payloadHash,
            idempotencyKey: $idempotencyKey,
            status: 'pending',
            retryCount: 0,
            originalRequestId: $original->id,
            replayIndex: $replayIndex,
            triggeredByType: 'human',
            triggeredBy: $actorUserId,
            triggerReason: $triggerReason,
            startedAt: null,
            finishedAt: null,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function itemDraftId(): ?string
{
    return $this->itemDraftId;
}
}
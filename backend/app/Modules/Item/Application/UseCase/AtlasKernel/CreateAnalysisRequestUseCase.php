<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;

final class CreateAnalysisRequestUseCase
{
    public function __construct(
        private AnalysisRequestRepository $requests,
    ) {}

    /**
     * v3 固定：
     * - analysis_requests 生成は必ずこのUseCaseを通す（= 必須キー欠落を根絶）
     * - payload_hash / idempotency_key を必ず生成
     * - item_draft_id は nullable（initial publishなら入る / legacyやreplayはnull許容）
     */
    public function handle(
        int $itemId,
        ?string $itemDraftId,
        string $rawText,
        ?int $tenantId,
        string $analysisVersion = 'v3_ai',
        string $triggeredByType = 'system', // system|human|policy
        ?int $triggeredBy = null,
        ?string $triggerReason = null,
        ?int $originalRequestId = null,
        ?int $replayIndex = null,
    ): int {
        $rawText = trim($rawText);

        // v3: idempotency の根は「入力＋version」
        $payloadHash = hash('sha256', implode('|', [
            (string) $tenantId,
            (string) $itemId,
            (string) ($itemDraftId ?? ''),
            $analysisVersion,
            $rawText,
        ]));

        // 外部idempotency_keyは用途でprefixを変える（将来運用をラクにする）
        $idempotencyKey = implode(':', array_filter([
            'atlas_v3',
            $analysisVersion,
            (string) $itemId,
            $payloadHash,
        ]));

        return $this->requests->create([
            'tenant_id'          => $tenantId,
            'item_id'            => $itemId,
            'item_draft_id'      => $itemDraftId, // nullable
            'analysis_version'   => $analysisVersion,
            'raw_text'           => $rawText,

            'payload_hash'       => $payloadHash,
            'idempotency_key'    => $idempotencyKey,

            // replay lineage（必要なときだけ）
            'original_request_id' => $originalRequestId,
            'replay_index'        => $replayIndex,

            // trigger
            'triggered_by_type'  => $triggeredByType,
            'triggered_by'       => $triggeredBy,
            'trigger_reason'     => $triggerReason,
        ]);
    }
}
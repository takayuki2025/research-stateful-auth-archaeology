<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use App\Modules\Item\Application\Query\AtlasReviewQuery;
use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Repository\ItemEntityRepository;
use App\Modules\Item\Domain\Event\Atlas\AtlasReviewApproved;
use App\Modules\Item\Domain\Event\Atlas\AtlasReviewRejected;
use App\Modules\Item\Domain\Event\Atlas\AtlasReviewEditConfirmed;
use App\Modules\Item\Domain\Event\Atlas\AtlasManualOverrideOccurred;
use App\Policies\AtlasDecisionPolicy;
use DomainException;
use Illuminate\Support\Facades\DB;

final class DecideAtlasReviewUseCase
{
    public function __construct(
        private AtlasReviewQuery $reviewQuery,
        private ReviewDecisionRepository $decisions,
        private AnalysisRequestRepository $requests,
        private ItemEntityRepository $itemEntityRepository,
        private AtlasDecisionPolicy $decisionPolicy,
    ) {}

    public function handle(
        string $shopCode,
        int $analysisRequestId,
        string $decisionType,
        ?array $afterSnapshot,
        ?string $note,
        int $actorUserId,
        string $actorRole,
    ): void {
        // ---- 0) confidence（UIと同じ計算：最大値）----
        $maxConfidence = collect($afterSnapshot ?? [])
            ->map(function ($v) {
                // afterSnapshotの形は attr=>{value,confidence...} or list どちらでも吸収
                if (is_array($v)) return $v['confidence'] ?? null;
                return null;
            })
            ->filter(fn ($v) => is_numeric($v))
            ->max();

        // ---- 1) DecisionPolicy（責任を Domainへ）----
        $this->decisionPolicy->assertCanDecide(
            decisionType: $decisionType,
            actorRole: $actorRole,
            maxConfidence: $maxConfidence !== null ? (float)$maxConfidence : null,
        );

        // ---- 2) Guard：解析が終わっていないなら決定不可 ----
        $status = $this->requests->getStatus($analysisRequestId);
        if ($status !== 'done') {
            throw new DomainException('Cannot decide: analysis is not done.');
        }

        // ---- 3) Guard：二重決定防止（v3最小）----
        $latest = $this->decisions->latestDecisionType($analysisRequestId);
        if ($latest !== null) {
            throw new DomainException('This request is already decided.');
        }

        // ---- 4) 入力要件 ----
        if (in_array($decisionType, ['edit_confirm', 'manual_override'], true)) {
            if (!$afterSnapshot || !is_array($afterSnapshot) || count($afterSnapshot) === 0) {
                throw new DomainException('after_snapshot is required for edit_confirm/manual_override.');
            }
        }

        DB::transaction(function () use (
            $shopCode,
            $analysisRequestId,
            $decisionType,
            $afterSnapshot,
            $note,
            $actorUserId,
            $actorRole
        ) {
            // ---- 5) Before 取得（item_entities/tagsへ寄せる Query を利用）----
            $src = $this->reviewQuery->fetchReviewSource(
                shopCode: $shopCode,
                analysisRequestId: $analysisRequestId,
            );

            $before = $src['before'] ?? null;

            // ---- 6) decision ledger 保存 ----
            $this->decisions->appendDecision(
                analysisRequestId: $analysisRequestId,
                decisionType: $decisionType,
                beforeSnapshot: is_array($before) ? $before : null,
                afterSnapshot: $afterSnapshot,
                note: $note,
                actorUserId: $actorUserId,
                actorRole: $actorRole,
            );

            // ---- 7) approve 系は SoT 反映（item_entities/タグへ）----
            if (in_array($decisionType, ['approve', 'system_approve'], true)) {
                $this->itemEntityRepository->applyAnalysisResult(
                    analysisRequestId: $analysisRequestId,
                    actorUserId: $actorUserId,
                );
            }

            // ---- 8) Event（通知/監査/学習へ接続）----
            match ($decisionType) {
                'approve', 'system_approve'
                    => event(new AtlasReviewApproved($analysisRequestId, $actorUserId, $actorRole)),

                'reject'
                    => event(new AtlasReviewRejected($analysisRequestId, $actorUserId, $actorRole, $note)),

                'edit_confirm'
                    => event(new AtlasReviewEditConfirmed($analysisRequestId, $actorUserId, $actorRole, $afterSnapshot ?? [])),

                'manual_override'
                    => event(new AtlasManualOverrideOccurred($analysisRequestId, $actorUserId, $actorRole, $afterSnapshot ?? [], $note)),

                default => null,
            };
        });
    }
}
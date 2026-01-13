<?php

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use Carbon\CarbonImmutable;
use App\Modules\Review\Domain\Entity\ReviewDecision;
use App\Modules\Review\Domain\Repository\ReviewDecisionRepository;

final class DecideAtlasRequestUseCase
{
    public function __construct(
        private ReviewDecisionRepository $decisions,
        private ApplyAnalysisResultUseCase $applyUseCase,
    ) {}

    public function handle(
        int $analysisRequestId,
        int $itemId,
        string $decisionType, // approve | edit_confirm | reject
        ?array $beforeSnapshot,
        ?array $afterSnapshot,
        ?string $note,
        int $userId,
    ): void {
        DB::transaction(function () use (
            $analysisRequestId,
            $itemId,
            $decisionType,
            $beforeSnapshot,
            $afterSnapshot,
            $note,
            $userId,
        ) {

            // ① ledger 保存（必須）
            $decision = new ReviewDecision(
                analysisRequestId: $analysisRequestId,
                decisionType: $decisionType,
                decisionReason: null,
                note: $note,
                beforeSnapshot: $beforeSnapshot,
                afterSnapshot: $afterSnapshot,
                decidedByType: 'human',
                decidedBy: $userId,
                decidedAt: CarbonImmutable::now(),
                subjectType: 'item',
                subjectId: $itemId,
            );

            $this->decisions->save($decision);

            // ② SoT 反映（approve / edit_confirm のみ）
            if (in_array($decisionType, ['approve', 'edit_confirm'], true)) {
                $finalTags = $afterSnapshot ?? $beforeSnapshot ?? [];

                $this->applyUseCase->handle(
                    itemId: $itemId,
                    decidedUserId: $userId,
                    finalTags: $finalTags,
                );
            }

            // reject は ledger のみ（SoT は触らない）
        });
    }
}
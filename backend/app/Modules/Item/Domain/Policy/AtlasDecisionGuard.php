<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Policy;

use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;
use DomainException;

final class AtlasDecisionGuard
{
    public function __construct(
        private ReviewDecisionRepository $decisions
    ) {}

    public function assertDecidable(int $analysisRequestId, string $decisionType): void
    {
        // 例：二重決定禁止（最新decisionが既に approve/reject 済なら弾く）
        $latest = $this->decisions->findLatestByAnalysisRequestId($analysisRequestId);

        if (!$latest) {
            return; // 初回はOK
        }

        $finalTypes = ['approve', 'system_approve', 'reject'];
        if (in_array($latest['decision_type'], $finalTypes, true)) {
            throw new DomainException('Already decided. You cannot decide twice.');
        }

        // edit_confirm/manual_override が連続するのを禁止したい等、ここに追加
    }
}
<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Query;

use App\Modules\Item\Domain\Model\ReviewDecision;

final class ReviewDecisionQuery
{
    /**
     * Cフェーズ：判断履歴（読み取り専用）
     */
    public function listByRequestId(int $requestId): array
    {
        return ReviewDecision::query()
            ->where('analysis_request_id', $requestId)
            ->orderByDesc('decided_at')
            ->get([
                'id',
                'decision_type',
                'decision_reason',
                'note',
                'decided_by_type',
                'decided_by',
                'decided_at',
            ])
            ->toArray();
    }
}
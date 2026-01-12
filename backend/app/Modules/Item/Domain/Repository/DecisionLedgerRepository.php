<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Repository;

use App\Modules\Item\Domain\Entity\DecisionLedger;

interface DecisionLedgerRepository
{
    /**
     * すでに決定がある場合は例外（Aルートは 1 request = 1 decision）
     */
    public function create(
        int $analysisRequestId,
        int $decidedUserId,
        string $decidedBy,   // 'human'
        string $decision,    // 'approved' | 'rejected'
        ?string $reason
    ): DecisionLedger;

    public function existsForRequest(int $analysisRequestId): bool;
}
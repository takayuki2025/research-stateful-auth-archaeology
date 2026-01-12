<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Modules\Item\Domain\Entity\DecisionLedger;
use App\Modules\Item\Domain\Repository\DecisionLedgerRepository;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class EloquentDecisionLedgerRepository implements DecisionLedgerRepository
{
    public function existsForRequest(int $analysisRequestId): bool
    {
        return DB::table('decision_ledgers')
            ->where('analysis_request_id', $analysisRequestId)
            ->exists();
    }

    public function create(
        int $analysisRequestId,
        int $decidedUserId,
        string $decidedBy,
        string $decision,
        ?string $reason
    ): DecisionLedger {
        $now = CarbonImmutable::now();

        try {
            $id = DB::table('decision_ledgers')->insertGetId([
                'analysis_request_id' => $analysisRequestId,
                'decided_user_id'     => $decidedUserId,
                'decided_by'          => $decidedBy,
                'decision'            => $decision,
                'reason'              => $reason,
                'decided_at'          => $now,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        } catch (QueryException $e) {
            // unique(analysis_request_id) 衝突 → すでに決定済み
            throw $e;
        }

        return new DecisionLedger(
            id: (int)$id,
            analysisRequestId: $analysisRequestId,
            decidedUserId: $decidedUserId,
            decidedBy: $decidedBy,
            decision: $decision,
            reason: $reason,
            decidedAt: $now,
        );
    }
}
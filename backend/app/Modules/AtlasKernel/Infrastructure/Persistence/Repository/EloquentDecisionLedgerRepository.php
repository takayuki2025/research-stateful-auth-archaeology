<?php

declare(strict_types=1);

namespace App\Modules\AtlasKernel\Infrastructure\Persistence\Repository;

use Illuminate\Support\Facades\DB;
use LogicException;

use App\Modules\AtlasKernel\Domain\Repository\DecisionLedgerRepository;

final class EloquentDecisionLedgerRepository implements DecisionLedgerRepository
{
    private const TABLE = 'review_decisions';

    public function append(array $payload): int
    {
        $id = DB::table(self::TABLE)->insertGetId([
            'analysis_request_id' => $payload['analysis_request_id'],
            'decision_type'       => $payload['decision_type'],
            'resolved_entities'   => $payload['resolved_entities'], // json/array 想定（cast はDBが処理）
            'after_snapshot'      => $payload['after_snapshot'],    // json/array 想定
            'note'                => $payload['note'] ?? null,
            'decided_by_type'     => $payload['decided_by_type'],
            'decided_by'          => $payload['decided_by'],
            'decided_at'          => $payload['decided_at'],
            'created_at'          => $payload['created_at'] ?? now(),
            'updated_at'          => $payload['updated_at'] ?? now(),
        ]);

        return (int)$id;
    }

    public function findLatestByAnalysisRequestId(int $analysisRequestId): ?object
    {
        // decided_at 優先、同値なら id で決める（固定）
        $row = DB::table(self::TABLE)
            ->where('analysis_request_id', $analysisRequestId)
            ->orderByDesc('decided_at')
            ->orderByDesc('id')
            ->first();

        return $row ?: null;
    }

    public function updateResolvedEntities(int $decisionId, array $resolvedEntities): void
    {
        $updated = DB::table(self::TABLE)
            ->where('id', $decisionId)
            ->update([
                'resolved_entities' => $resolvedEntities,
                'updated_at'        => now(),
            ]);

        if ($updated === 0) {
            throw new LogicException("Decision not found for update: id={$decisionId}");
        }
    }
}
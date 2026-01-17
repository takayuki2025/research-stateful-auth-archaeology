<?php

declare(strict_types=1);

namespace App\Modules\AtlasKernel\Domain\Repository;

interface DecisionLedgerRepository
{
    /**
     * append a decision ledger entry
     * @return int created decision id
     */
    public function append(array $payload): int;

    /**
     * latest decision by analysis_request_id
     * @return object|null (id, decision_type, resolved_entities, after_snapshot, note, decided_by_type, decided_by, decided_at)
     */
    public function findLatestByAnalysisRequestId(int $analysisRequestId): ?object;

    /**
     * update resolved_entities for an existing decision
     */
    public function updateResolvedEntities(int $decisionId, array $resolvedEntities): void;
}
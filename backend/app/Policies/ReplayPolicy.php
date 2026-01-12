<?php

declare(strict_types=1);

namespace App\Policies;

use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use Carbon\CarbonImmutable;
use DomainException;

final class ReplayPolicy
{
    public function __construct(
        private AnalysisRequestRepository $analysisRequests,
    ) {}

    /**
     * Replay 可能かを検証
     *
     * @throws DomainException
     */
    public function assertCanReplay(
        int $originalRequestId,
        string $actorRole,
    ): void {

        // ✅ 無制限ロール
        if (in_array($actorRole, [
            'owner',
            'domain_lead_admin',
            'supervisor_admin',
            'system_manager_admin',
        ], true)) {
            return;
        }

        // ✅ 制限付き（Manager）
        if ($actorRole === 'manager') {
            $this->assertManagerReplayLimit($originalRequestId);
            return;
        }

        throw new DomainException('You are not allowed to replay analysis.');
    }

    /**
     * Manager 用：1 年間で最大 4 回
     */
    private function assertManagerReplayLimit(int $originalRequestId): void
    {
        $original = $this->analysisRequests->getById($originalRequestId);

        $from = CarbonImmutable::parse($original->created_at);
        $to   = $from->addYear();

        $count = $this->analysisRequests->countReplaysInPeriod(
            originalRequestId: $originalRequestId,
            from: $from,
            to: $to,
        );

        if ($count >= 4) {
            throw new DomainException(
                'Replay limit exceeded (max 4 times per year for managers).'
            );
        }
    }
}
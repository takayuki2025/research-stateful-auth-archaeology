<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use App\Modules\Item\Domain\Entity\AnalysisRequest;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Policies\ReplayPolicy;
use App\Modules\Item\Application\Job\AnalyzeItemForReviewJob; // ✅ v3固定：唯一の解析Job
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class ReplayAnalysisRequestUseCase
{
    public function __construct(
        private ReplayPolicy $policy,
        private AnalysisRequestRepository $requests,
    ) {}

    public function handle(
        int $originalRequestId,
        string $requestedVersion,
        string $actorRole,
        int $actorUserId,
        string $triggerReason,
    ): int {
        $this->policy->assertCanReplay(
            originalRequestId: $originalRequestId,
            actorRole: $actorRole,
        );

        return DB::transaction(function () use (
            $originalRequestId,
            $requestedVersion,
            $actorUserId,
            $triggerReason
        ) {
            $original = $this->requests->getById($originalRequestId);

            $from = CarbonImmutable::instance($original->createdAt);
            $to   = $from->addYear();

            $replayIndex = $this->requests->countReplaysInPeriod(
                originalRequestId: $originalRequestId,
                from: $from,
                to: $to,
            ) + 1;

            $replay = AnalysisRequest::replayFrom(
                original: $original,
                requestedVersion: $requestedVersion,
                replayIndex: $replayIndex,
                actorUserId: $actorUserId,
                triggerReason: $triggerReason,
            );

            // ✅ $newId = analysis_request_id（requestId主語）
            $newId = $this->requests->save($replay);

            // ✅ v3固定：AnalyzeItemForReviewJob が唯一の解析Job
            AnalyzeItemForReviewJob::dispatch($newId, 'replay');

            return $newId;
        });
    }
}

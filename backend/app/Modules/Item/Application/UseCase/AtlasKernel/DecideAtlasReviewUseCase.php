<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use App\Modules\Item\Application\Query\AtlasReviewQuery;
use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;
use App\Modules\Item\Domain\Repository\ItemEntityRepository;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Policy\AtlasDecisionPolicy;
use App\Modules\Item\Domain\Policy\AtlasDecisionGuard;
use App\Modules\Item\Domain\Event\Atlas\AtlasReviewApproved;
use App\Modules\Item\Domain\Event\Atlas\AtlasReviewRejected;
use App\Modules\Item\Domain\Event\Atlas\AtlasReviewEditConfirmed;
use App\Modules\Item\Domain\Event\Atlas\AtlasManualOverrideOccurred;
use App\Modules\Item\Application\UseCase\AtlasKernel\ApplyConfirmedDecisionUseCase;
use Illuminate\Support\Facades\DB;

final class DecideAtlasReviewUseCase
{
    public function __construct(
    private AtlasReviewQuery $reviewQuery,
    private ReviewDecisionRepository $decisions,
    private AnalysisRequestRepository $requests,
    private AtlasDecisionPolicy $policy,
    private AtlasDecisionGuard $guard,
    private ApplyConfirmedDecisionUseCase $applyConfirmedDecision, // ← 追加
) {}

    public function handle(
        string $shopCode,
        int $analysisRequestId,
        string $decisionType,        // approve|reject|edit_confirm|manual_override|system_approve
        ?array $afterSnapshot,
        ?string $note,
        int $actorUserId,
        string $actorRole,
    ): void {

        // 0) 入力の最小バリデーション（UX仕様に一致）
        if (in_array($decisionType, ['edit_confirm', 'manual_override'], true)) {
            if (!$afterSnapshot || !is_array($afterSnapshot) || count($afterSnapshot) === 0) {
                throw new \DomainException('after_snapshot is required for edit_confirm/manual_override.');
            }
        }

        // 1) confidence×role を Domain Policy に完全移譲
        $maxConfidence = collect($afterSnapshot ?? [])
            ->pluck('confidence')
            ->filter(fn ($v) => is_numeric($v))
            ->map(fn ($v) => (float) $v)
            ->max();

        $this->policy->assertCanDecide(
            decisionType: $decisionType,
            actorRole: $actorRole,
            maxConfidence: $maxConfidence,
        );

        DB::transaction(function () use (
            $shopCode,
            $analysisRequestId,
            $decisionType,
            $afterSnapshot,
            $note,
            $actorUserId,
            $actorRole
        ) {

            // 2) Guard（状態/二重決定/整合性）
            $this->guard->assertDecidable(
                analysisRequestId: $analysisRequestId,
                decisionType: $decisionType,
            );

            // 3) before を確定（UIの強化にも直結）
            $src = $this->reviewQuery->fetchReviewSource(
                shopCode: $shopCode,
                analysisRequestId: $analysisRequestId
            );
            $before = $src['before'] ?? null;

            // 4) ledger 保存（review_decisions）
            $this->decisions->appendDecision([
    'analysis_request_id' => $analysisRequestId,
    'decision_type'       => $decisionType,
    'decision_reason'     => null, // 将来用
    'note'                => $note,
    'before_snapshot'     => is_array($before) ? $before : null,
    'after_snapshot'      => $afterSnapshot,
    'decided_by_type'     => 'human',
    'decided_by'          => $actorUserId,
    'decided_at'          => now(),
]);


            // 5) approve / edit_confirm のときだけ SoT へ反映
if (in_array($decisionType, ['approve', 'edit_confirm'], true)) {
    $this->applyConfirmedDecision->handle($analysisRequestId);
}

            // 6) イベント（通知/監査/学習）
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
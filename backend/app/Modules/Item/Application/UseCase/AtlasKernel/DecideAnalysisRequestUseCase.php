<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use App\Modules\Item\Application\Dto\AtlasKernel\DecideAnalysisRequestInput;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use App\Modules\Item\Domain\Repository\DecisionLedgerRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

final class DecideAnalysisRequestUseCase
{
    public function __construct(
        private AnalysisRequestRepository $requests,
        private AnalysisResultRepository $results,
        private DecisionLedgerRepository $ledgers,
    ) {
    }

    public function handle(DecideAnalysisRequestInput $input): void
    {
        if (!in_array($input->decision, ['approved', 'rejected'], true)) {
            throw new \InvalidArgumentException('decision must be approved or rejected');
        }

        DB::transaction(function () use ($input) {
            // request の存在確認（Shop境界の検証は Controller の authorize で担保）
            $request = $this->requests->findOrFail($input->requestId);

            // すでに決定済みなら弾く（Aルートは 1回のみ）
            if ($this->ledgers->existsForRequest($request->id)) {
                throw new \RuntimeException('Already decided for this request.');
            }

            // Decision Ledger に記録（責任台帳）
            try {
                $this->ledgers->create(
                    analysisRequestId: $request->id,
                    decidedUserId: $input->decidedUserId,
                    decidedBy: $input->decidedBy,
                    decision: $input->decision,
                    reason: $input->reason
                );
            } catch (QueryException $e) {
                // unique衝突など
                throw new \RuntimeException('Already decided for this request.');
            }

            // Aルート：analysis_results の active を decided/rejected にする
            // ここはあなたの既存メソッドを活用
            if ($input->decision === 'approved') {
                // approved → decided
                // decided_by / decided_user_id を残す（あなたの既存仕様に合わせる）
                $this->results->markDecidedByRequest(
    analysisRequestId: $request->id(),
    decidedBy: $input->decidedBy,
    decidedUserId: $input->decidedUserId
);
            } else {
                // rejected
                $this->results->markRejectedByRequest(
    analysisRequestId: $request->id()
);
            }
        });
    }
}
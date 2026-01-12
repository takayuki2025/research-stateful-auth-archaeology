<?php

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use App\Modules\Item\Domain\Model\ReviewDecision;
use Illuminate\Http\JsonResponse;

final class AtlasDecisionHistoryController extends Controller
{
    public function history(
        string $shopCode,
        int $requestId
    ): JsonResponse {
        $decisions = ReviewDecision::where(
                'analysis_request_id',
                $requestId
            )
            ->orderByDesc('decided_at')
            ->get([
                'id',
                'decision_type',
                'decision_reason',
                'note',
                'decided_by_type',
                'decided_by',
                'decided_at',
            ]);

        return response()->json([
            'request_id' => $requestId,
            'decisions' => $decisions,
        ]);
    }

    /** Dフェーズ（仮） */
    public function replay(
        string $shopCode,
        int $requestId
    ): JsonResponse {
        // まだ実装しない
        return response()->json([
            'status' => 'accepted',
        ], 202);
    }
}
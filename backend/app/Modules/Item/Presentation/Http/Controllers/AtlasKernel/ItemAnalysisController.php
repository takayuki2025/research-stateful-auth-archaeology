<?php

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Modules\Item\Domain\Service\AtlasKernelService;
use App\Modules\Item\Application\UseCase\AtlasKernel\SaveAnalysisResultUseCase;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;

final class ItemAnalysisController extends Controller
{
    public function reanalyze(
        int $itemId,
        AtlasKernelService $atlas,
        SaveAnalysisResultUseCase $save,
        AnalysisRequestRepository $requests
    ) {
        $item = Item::findOrFail($itemId);

        // 新規 request を作る（v3固定）
        $requestId = $requests->create([
            'item_id'          => $itemId,
            'status'           => 'pending',
            'analysis_version' => 'v3_ai',
        ]);

        $result = $atlas->requestAnalysis(
            $itemId,
            "{$item->name} {$item->explain}",
            $requestId
        );

        $save->handle(
            analysisRequestId: $requestId,
            itemId: $itemId,
            analysisPayload: $result->toArray()
        );

        return response()->json([
            'status' => 'reanalyzed',
            'request_id' => $requestId,
        ]);
    }
}
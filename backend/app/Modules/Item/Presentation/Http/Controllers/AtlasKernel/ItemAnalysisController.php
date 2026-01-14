<?php

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Application\Job\AnalyzeItemForReviewJob;

final class ItemAnalysisController extends Controller
{
    public function reanalyze(
        int $itemId,
        AnalysisRequestRepository $requests
    ) {
        $item = Item::findOrFail($itemId);

        // v3 固定：AnalysisRequest を作るだけ
        $requestId = $requests->create([
            'item_id'          => $itemId,
            'item_draft_id'    => (string) $item->draft_id,
            'raw_text'         => "{$item->name} {$item->explain}",
            'analysis_version' => 'v3_ai',
            'status'           => 'pending',
        ]);

        // v3 固定：Job に投げる
        AnalyzeItemForReviewJob::dispatch($requestId);

        return response()->json([
            'status'     => 'queued',
            'request_id' => $requestId,
        ]);
    }
}

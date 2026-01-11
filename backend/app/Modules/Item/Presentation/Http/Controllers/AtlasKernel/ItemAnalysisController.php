<?php

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Modules\Item\Domain\Service\AtlasKernelService;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;

final class ItemAnalysisController extends Controller
{
    public function reanalyze(
        int $itemId,
        AtlasKernelService $atlas,
        AnalysisResultRepository $repo
    ) {
        $item = Item::findOrFail($itemId);

        $result = $atlas->requestAnalysis(
            $itemId,
            "{$item->name} {$item->explain}",
            null
        );

        // 旧結果を superseded
        $repo->markRejected($itemId);

        // 新しい provisional を保存
        $repo->save($itemId, [
            'analysis' => $result->toArray(),
            'status'   => 'provisional',
            'strategy' => 'v4_reanalyze',
        ]);

        return response()->json([
            'status' => 'reanalyzed',
        ]);
    }
}
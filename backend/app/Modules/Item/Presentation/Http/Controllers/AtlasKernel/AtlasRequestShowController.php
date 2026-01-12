<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use Illuminate\Http\JsonResponse;
use App\Models\Shop as ShopModel;

final class AtlasRequestShowController extends Controller
{
    public function __invoke(
        string $shop_code,
        int $request_id,
        AnalysisRequestRepository $requests,
        AnalysisResultRepository $results
    ): JsonResponse {
        // ① 認可（Eloquent Shop）
        $shopModel = ShopModel::where('shop_code', $shop_code)->firstOrFail();
        $this->authorize('review', $shopModel);

        // ② Domain Request
        $request = $requests->findOrFail($request_id);

        // ③ Bフェーズ正解：request_id 起点
        $analysis = $results->findByRequestId($request->id);

        return response()->json([
    'request' => [
        'id'               => $request->id,
        'item_id'          => $request->itemId,
        'status'           => $request->status,
        'analysis_version' => $request->analysisVersion, // ✅ ここ
    ],
    'analysis' => $analysis ? [
        'decision'   => data_get($analysis->payload, 'decision'),
        'rule_id'    => data_get($analysis->payload, 'rule'),
        'confidence' => data_get($analysis->confidence, 'brand'),
    ] : null,
]);
    }
}
<?php

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use App\Http\Controllers\Controller;
use App\Models\Shop as ShopModel;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AtlasRequestListController extends Controller
{
    public function __invoke(
        Request $request,
        string $shop_code,
        AnalysisRequestRepository $requests,
    ): JsonResponse {
        // 認可用：Eloquent Shop
        $shopModel = ShopModel::where('shop_code', $shop_code)->firstOrFail();

        // ability は list に統一
        $this->authorize('list', $shopModel);

        // 一覧取得（Repository 側で shop_code を使う）
        $list = $requests->listByShopCode($shop_code);

        return response()->json([
            'requests' => $list,
        ]);
    }
}
<?php

namespace App\Modules\Item\Presentation\Http\Controllers\AtlasKernel;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Shop\Domain\Repository\ShopRepository;
use App\Models\Shop as ShopModel;

final class AtlasRequestListController extends Controller
{
    public function __invoke(
        Request $request,
        string $shop_code,
        AnalysisRequestRepository $requests,
        ShopRepository $shops
    ): JsonResponse {
        $shopModel = ShopModel::where('shop_code', $shop_code)->firstOrFail();

        // ability は A ルートで統一
        $this->authorize('list', $shopModel);

        $list = $requests->listByShopCode($shop_code);

        return response()->json([
            'requests' => $list,
        ]);
    }
}
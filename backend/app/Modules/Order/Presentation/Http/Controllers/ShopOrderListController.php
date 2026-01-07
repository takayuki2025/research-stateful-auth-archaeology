<?php

namespace App\Modules\Order\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Domain\Repository\OrderQueryRepository;
use App\Modules\Shop\Application\Dto\ShopContext;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

final class ShopOrderListController extends Controller
{
    public function __invoke(
        Request $request,
        OrderQueryRepository $orders
    ): JsonResponse {
        /** @var ShopContext|null $ctx */
        $ctx = $request->attributes->get(ShopContext::class);

        if (! $ctx) {
            abort(500, 'ShopContext missing');
        }

        return response()->json([
            'orders' => $orders->findOrderListWithShipmentByShopId(
                $ctx->shopId
            ),
        ]);
    }
}

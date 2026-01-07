<?php

namespace App\Modules\Order\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Modules\Order\Application\UseCase\GetShopOrderShipmentUseCase;
use App\Modules\Shop\Application\Dto\ShopContext;
use Illuminate\Support\Facades\Log;

final class ShopOrderShipmentController extends Controller
{
    public function __invoke(
        Request $request,
        string $shop_code,
        string $orderId,
        GetShopOrderShipmentUseCase $useCase
    ) {
        /** @var ShopContext|null $ctx */
        $ctx = $request->attributes->get(ShopContext::class);
        if (! $ctx) {
            abort(500);
        }

        $view = $useCase->handle(
            shopId: $ctx->shopId,
            orderId: (int) $orderId
        );

        return response()->json(
            $view->toArray()
        );
    }
}
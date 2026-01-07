<?php

namespace App\Modules\Shipment\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Modules\Shipment\Application\UseCase\CreateShipmentUseCase;
use App\Modules\Shop\Application\Dto\ShopContext;

final class ShipmentController extends Controller
{
    public function __construct(
        private CreateShipmentUseCase $useCase
    ) {
    }

    public function store(
        Request $request,
        string $shop_code,
        string $orderId
    ) {
        /** @var ShopContext|null $ctx */
        $ctx = $request->attributes->get(ShopContext::class);

        if (! $ctx) {
            abort(500, 'ShopContext not resolved');
        }

        $this->useCase->handle(
            orderId: (int) $orderId,
            shopId: $ctx->shopId,
        );

        return response()->json([
            'status' => 'ok',
        ], 201);
    }
}

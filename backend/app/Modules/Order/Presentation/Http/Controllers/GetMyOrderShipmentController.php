<?php

namespace App\Modules\Order\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Order\Application\UseCase\GetMyOrderShipmentUseCase;

final class GetMyOrderShipmentController extends Controller
{
    public function __invoke(
        Request $request,
        int $orderId,
        GetMyOrderShipmentUseCase $useCase
    ) {
        $userId = $request->user()->id;

        $shipment = $useCase->handle(
            userId: $userId,
            orderId: $orderId
        );

        if (! $shipment) {
            abort(404);
        }

        return response()->json($shipment);
    }
}

<?php

namespace App\Modules\Shipment\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shipment\Application\UseCase\ShipShipmentUseCase;

final class ShipShipmentController extends Controller
{
    public function __invoke(
        int $shipmentId,
        ShipShipmentUseCase $useCase
    ) {
        $useCase->handle($shipmentId);

        return response()->json(['status' => 'shipped']);
    }
}

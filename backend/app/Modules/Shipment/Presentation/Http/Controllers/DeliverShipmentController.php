<?php

namespace App\Modules\Shipment\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shipment\Application\UseCase\DeliverShipmentUseCase;

final class DeliverShipmentController extends Controller
{
    public function __invoke(
        int $shipmentId,
        DeliverShipmentUseCase $useCase
    ) {
        $useCase->handle($shipmentId);

        return response()->json(['status' => 'delivered']);
    }
}

<?php

namespace App\Modules\Shipment\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shipment\Application\UseCase\InTransitShipmentUseCase;
use DomainException;

final class InTransitShipmentController extends Controller
{
    public function __invoke(
        int $shipmentId,
        InTransitShipmentUseCase $useCase
    ) {
        try {
            $useCase->handle($shipmentId);

            return response()->json(['result' => 'ok']);
        } catch (DomainException $e) {
            return response()->json([
                'error' => 'invalid_state',
                'message' => $e->getMessage(),
            ], 409);
        }
    }
}

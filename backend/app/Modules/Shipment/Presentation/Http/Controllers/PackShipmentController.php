<?php

namespace App\Modules\Shipment\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shipment\Application\UseCase\PackShipmentUseCase;
use DomainException;

final class PackShipmentController extends Controller
{
    public function __invoke(
        int $shipmentId,
        PackShipmentUseCase $useCase
    ) {
        try {
            $useCase->handle($shipmentId); // ← pack() ではない

            return response()->json(['result' => 'ok']);
        } catch (DomainException $e) {
            return response()->json([
                'error'   => 'invalid_state',
                'message' => $e->getMessage(),
            ], 409);
        }
    }
}

<?php

namespace App\Modules\Shipment\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Modules\Shipment\Domain\Repository\ShipmentRepository;
use App\Modules\Shipment\Domain\Repository\ShipmentEventRepository;

final class CustomerShipmentController
{
    public function show(
        int $shipmentId,
        ShipmentRepository $shipments,
        ShipmentEventRepository $events
    ): JsonResponse {
        $shipment = $shipments->find($shipmentId);

        return response()->json([
            'status' => $shipment->status->value,
            'eta' => $shipment->eta,
            'timeline' => $events->timeline($shipmentId),
        ]);
    }
}

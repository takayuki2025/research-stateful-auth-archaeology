<?php

namespace App\Modules\Shipment\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shipment\Application\UseCase\CreateShipmentDraftUseCase;
use App\Modules\Shipment\Application\Dto\CreateShipmentDraftInput;
use Illuminate\Http\Response;

final class CreateShipmentDraftController extends Controller
{
    public function __construct(
        private CreateShipmentDraftUseCase $useCase
    ) {
    }

    public function __invoke(int $shopId, int $orderId): Response
    {
        $this->useCase->handle(
            new CreateShipmentDraftInput(
                orderId: $orderId,
                shopId: $shopId,
            )
        );

        return response()->noContent();
    }
}

<?php

namespace App\Modules\Shipment\Application\UseCase;

use App\Modules\Shipment\Domain\Repository\ShipmentRepository;
use App\Modules\Shipment\Domain\Enum\ShipmentStatus;
use DomainException;


final class ZZZ
{
    public function __construct(
        private ShipmentRepository $shipments,
    ) {
    }

    private function deprecated(): never
    {
        throw new \LogicException(
            'UpdateShipmentStatusUseCase is deprecated. Use PackShipmentUseCase / ShipShipmentUseCase instead.'
        );
    }

    public function pack(int $shipmentId): void
    {
        $this->deprecated();
    }

    public function ship(int $shipmentId): void
    {
        $this->deprecated();
    }

    public function inTransit(int $shipmentId): void
    {
        $this->deprecated();
    }

    public function deliver(int $shipmentId): void
    {
        $this->deprecated();
    }
}

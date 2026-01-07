<?php

namespace App\Modules\Shipment\Domain\Exception;

use DomainException;

final class ShipmentAlreadyExistsException extends DomainException
{
    public function __construct(
        string $message = 'Shipment already exists for this order.'
    ) {
        parent::__construct($message);
    }
}

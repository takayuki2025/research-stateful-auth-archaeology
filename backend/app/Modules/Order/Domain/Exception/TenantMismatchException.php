<?php

namespace App\Modules\Order\Domain\Exception;

use DomainException;

final class TenantMismatchException extends DomainException
{
    public function __construct(
        string $message = 'Order does not belong to this shop'
    ) {
        parent::__construct($message);
    }
}

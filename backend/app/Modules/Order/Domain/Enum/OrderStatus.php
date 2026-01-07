<?php

namespace App\Modules\Order\Domain\Enum;

enum OrderStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case PAID            = 'paid';
    case PAYMENT_FAILED  = 'payment_failed';
    case CANCELLED       = 'cancelled';
    case EXPIRED         = 'expired';
}

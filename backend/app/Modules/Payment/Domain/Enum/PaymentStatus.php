<?php

namespace App\Modules\Payment\Domain\Enum;

enum PaymentStatus: string
{
    case INITIATED       = 'initiated';
    case REQUIRES_ACTION = 'requires_action'; // 3DS etc.
    case SUCCEEDED       = 'succeeded';
    case FAILED          = 'failed';
    case CANCELLED       = 'cancelled';
}

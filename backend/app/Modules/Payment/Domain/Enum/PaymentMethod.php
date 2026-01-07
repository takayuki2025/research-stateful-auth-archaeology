<?php

namespace App\Modules\Payment\Domain\Enum;

enum PaymentMethod: string
{
    case CARD    = 'card';
    case KONBINI = 'konbini'; // dummy in v1
}

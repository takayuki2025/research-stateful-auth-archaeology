<?php

namespace App\Modules\Payment\Domain\Event;


enum LedgerType: string
{
    case SALE = 'sale';
    case REFUND = 'refund';
    case FEE = 'fee';
    case ADJUSTMENT = 'adjustment';
}

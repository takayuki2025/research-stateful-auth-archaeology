<?php

namespace App\Modules\Shop\Domain\Enum;

// LedgerType に意味を寄せる
enum LedgerType: string
{
    case SALE = 'sale';     // +amount
    case FEE = 'fee';       // -amount
    case REFUND = 'refund'; // -amount

    case PAYOUT = 'payout';
    case ADJUSTMENT = 'adjustment';
}

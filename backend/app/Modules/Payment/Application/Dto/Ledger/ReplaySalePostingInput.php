<?php

namespace App\Modules\Payment\Application\Dto\Ledger;

final class ReplaySalePostingInput
{
    public function __construct(
        public int $payment_id,
    ) {
    }
}
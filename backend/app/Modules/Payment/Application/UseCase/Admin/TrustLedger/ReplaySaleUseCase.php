<?php

namespace App\Modules\Payment\Application\UseCase\Admin\TrustLedger;

use App\Modules\Payment\Application\Dto\Ledger\ReplaySalePostingInput;
use App\Modules\Payment\Application\UseCase\Ledger\ReplaySalePostingUseCase;

final class ReplaySaleUseCase
{
    public function __construct(
        private ReplaySalePostingUseCase $replay,
    ) {
    }

    public function handle(int $paymentId): void
    {
        $this->replay->handle(new ReplaySalePostingInput(payment_id: $paymentId));
    }
}
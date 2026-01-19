<?php

namespace App\Modules\Payment\Application\UseCase\Admin\TrustLedger;

use App\Modules\Payment\Application\UseCase\Accounts\MarkPayoutStatusUseCase;

final class MarkPayoutStatusAdminUseCase
{
    public function __construct(
        private MarkPayoutStatusUseCase $useCase,
    ) {
    }

    public function handle(int $payoutId, string $status): void
    {
        $this->useCase->handle($payoutId, $status);
    }
}
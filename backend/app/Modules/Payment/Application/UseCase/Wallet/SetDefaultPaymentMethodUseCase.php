<?php

namespace App\Modules\Payment\Application\UseCase\Wallet;

use App\Modules\Payment\Domain\Repository\Wallet\WalletRepository;
use App\Modules\Payment\Domain\Repository\Wallet\StoredPaymentMethodRepository;
use Illuminate\Support\Facades\DB;

final class SetDefaultPaymentMethodUseCase
{
    public function __construct(
        private WalletRepository $wallets,
        private StoredPaymentMethodRepository $methods,
    ) {
    }

    public function handle(int $userId, int $methodId): void
    {
        $wallet = $this->wallets->findByUserId($userId);

        if (! $wallet || $wallet->id() === null) {
            throw new \DomainException('Wallet not found');
        }

        $row = $this->methods->findRowById($methodId);
        if (! $row) {
            throw new \DomainException('Payment method not found');
        }

        if ((int)$row['wallet_id'] !== (int)$wallet->id()) {
            throw new \DomainException('Forbidden');
        }

        if ($row['status'] !== 'active') {
            throw new \DomainException('Payment method is not active');
        }

        DB::transaction(function () use ($wallet, $methodId) {
            $this->methods->setDefault((int)$wallet->id(), $methodId);
        });
    }
}
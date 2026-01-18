<?php

namespace App\Modules\Payment\Application\UseCase\Wallet;

use App\Modules\Payment\Application\Dto\Wallet\WalletDto;
use App\Modules\Payment\Application\Dto\Wallet\PaymentMethodDto;
use App\Modules\Payment\Domain\Repository\Wallet\WalletRepository;
use App\Modules\Payment\Domain\Repository\Wallet\StoredPaymentMethodRepository;

final class ListPaymentMethodsUseCase
{
    public function __construct(
        private WalletRepository $wallets,
        private StoredPaymentMethodRepository $methods,
    ) {
    }

    public function handle(int $userId): WalletDto
    {
        $wallet = $this->wallets->findByUserId($userId);

        if (! $wallet || $wallet->id() === null) {
            // Wallet未作成でもクライアントは同一DTOで扱える
            return new WalletDto(false, []);
        }

        $pms = $this->methods->listActiveByWalletId($wallet->id());

        return new WalletDto(
            true,
            array_map(fn ($pm) => PaymentMethodDto::fromEntity($pm), $pms)
        );
    }
}
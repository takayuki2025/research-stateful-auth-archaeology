<?php

namespace App\Modules\Payment\Domain\Repository\Wallet;

use App\Modules\Payment\Domain\Entity\Wallet\CustomerWallet;

interface WalletRepository
{
    public function findByUserId(int $userId, string $provider = 'stripe'): ?CustomerWallet;

    public function findByProviderCustomerId(string $providerCustomerId, string $provider = 'stripe'): ?CustomerWallet;

    public function create(CustomerWallet $wallet): CustomerWallet;

    public function setProviderCustomerId(int $walletId, string $providerCustomerId): void;
}
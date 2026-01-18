<?php

namespace App\Modules\Payment\Application\UseCase\Wallet;

use App\Modules\Payment\Application\Dto\Wallet\CreateSetupIntentOutput;
use App\Modules\Payment\Domain\Repository\Wallet\WalletRepository;
use App\Modules\Payment\Domain\Service\PaymentMethodVault;
use App\Modules\Payment\Domain\Entity\Wallet\CustomerWallet;
use Illuminate\Support\Facades\DB;

final class CreateSetupIntentUseCase
{
    public function __construct(
        private WalletRepository $wallets,
        private PaymentMethodVault $vault,
    ) {
    }

    public function handle(int $userId, ?string $email = null, ?string $name = null): CreateSetupIntentOutput
    {
        return DB::transaction(function () use ($userId, $email, $name) {

            $wallet = $this->wallets->findByUserId($userId);

            if (! $wallet) {
                $wallet = $this->wallets->create(
                    CustomerWallet::createForUser($userId)
                );
            }

            $providerCustomerId = $wallet->providerCustomerId();

            if (!is_string($providerCustomerId) || $providerCustomerId === '') {
                $providerCustomerId = $this->vault->createCustomer($userId, $email, $name);
                $this->wallets->setProviderCustomerId((int)$wallet->id(), $providerCustomerId);
            }

            $si = $this->vault->createSetupIntent($providerCustomerId);

            return new CreateSetupIntentOutput(
                setup_intent_id: $si->setupIntentId,
                client_secret: $si->clientSecret,
            );
        });
    }
}
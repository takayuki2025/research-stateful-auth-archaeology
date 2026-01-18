<?php

namespace App\Modules\Payment\Application\UseCase\Wallet;

use App\Modules\Payment\Domain\Repository\Wallet\WalletRepository;
use App\Modules\Payment\Domain\Repository\Wallet\StoredPaymentMethodRepository;
use App\Modules\Payment\Domain\Service\PaymentMethodVault;
use Illuminate\Support\Facades\DB;

final class DetachPaymentMethodUseCase
{
    public function __construct(
        private WalletRepository $wallets,
        private StoredPaymentMethodRepository $methods,
        private PaymentMethodVault $vault,
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

        // すでにdetachedなら冪等OK（何もしない）
        if ($row['status'] === 'detached') {
            return;
        }

        $wasDefault = (bool)$row['is_default'];
        $provider = (string)$row['provider'];
        $providerPmId = (string)$row['provider_payment_method_id'];

        DB::transaction(function () use ($wallet, $methodId, $wasDefault) {
            $walletId = (int)$wallet->id();

            // DB側を先に detached にする（アプリの真実を正にする）
            $this->methods->markDetached($walletId, $methodId);

            // defaultだった場合、次のactiveをdefaultに繰り上げ
            if ($wasDefault) {
                $nextId = $this->methods->findNextActiveId($walletId, $methodId);
                if ($nextId !== null) {
                    $this->methods->setDefault($walletId, $nextId);
                }
            }
        });

        // Stripe detach は外部I/Oなのでトランザクション外で実行
        if ($provider === 'stripe') {
            try {
                $this->vault->detachPaymentMethod($providerPmId);
            } catch (\Throwable $e) {
                // DBは detached を正にし、Stripe側の失敗はログに残す
                \Log::warning('[Wallet Detach] Stripe detach failed', [
                    'method_id' => $methodId,
                    'provider_pm_id' => $providerPmId,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}

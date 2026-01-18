<?php

namespace App\Modules\Payment\Application\UseCase\Wallet;

use App\Modules\Payment\Application\Dto\HandlePaymentWebhookInput;
use App\Modules\Payment\Domain\Repository\PaymentQueryRepository;
use App\Modules\Payment\Domain\Repository\Wallet\WalletRepository;
use App\Modules\Payment\Domain\Repository\Wallet\StoredPaymentMethodRepository;
use App\Modules\Payment\Domain\Service\PaymentMethodVault;

final class HandleWalletWebhookUseCase
{
    public function __construct(
        private PaymentQueryRepository $webhookEvents, // processed 正統の reserve/complete を再利用
        private WalletRepository $wallets,
        private StoredPaymentMethodRepository $methods,
        private PaymentMethodVault $vault,
    ) {
    }

    public function handle(HandlePaymentWebhookInput $input): void
    {
        $reserved = $this->safeReserve($input);
        if ($reserved !== true) {
            return;
        }

        $status = 'ok';
        $error = null;

        try {
            $type = $input->eventType;

            if ($type === 'setup_intent.succeeded') {
                $this->handleSetupIntentSucceeded($input);
            } elseif ($type === 'payment_method.attached') {
                $this->handlePaymentMethodAttached($input);
            } else {
                $status = 'ignored';
            }
        } catch (\Throwable $e) {
            $status = 'error';
            $error = $e->getMessage();
            throw $e;
        } finally {
            $this->safeComplete($input, $status, null, null, $error);
        }
    }

    private function handleSetupIntentSucceeded(HandlePaymentWebhookInput $input): void
    {
        $object = $input->payload['data']['object'] ?? [];
        $providerCustomerId = $object['customer'] ?? null;
        $providerPaymentMethodId = $object['payment_method'] ?? null;

        if (!is_string($providerCustomerId) || $providerCustomerId === '') {
            return;
        }
        if (!is_string($providerPaymentMethodId) || $providerPaymentMethodId === '') {
            return;
        }

        $wallet = $this->wallets->findByProviderCustomerId($providerCustomerId);
        if (! $wallet || $wallet->id() === null) {
            return;
        }

        $card = $this->vault->retrievePaymentMethodCard($providerPaymentMethodId);

        $this->methods->upsertActiveCard(
            walletId: (int)$wallet->id(),
            provider: 'stripe',
            providerPaymentMethodId: $providerPaymentMethodId,
            brand: $card->brand,
            last4: $card->last4,
            expMonth: $card->expMonth,
            expYear: $card->expYear,
        );
    }

    private function handlePaymentMethodAttached(HandlePaymentWebhookInput $input): void
    {
        $object = $input->payload['data']['object'] ?? [];
        $providerCustomerId = $object['customer'] ?? null;
        $providerPaymentMethodId = $object['id'] ?? null;

        if (!is_string($providerCustomerId) || $providerCustomerId === '') {
            return;
        }
        if (!is_string($providerPaymentMethodId) || $providerPaymentMethodId === '') {
            return;
        }

        $wallet = $this->wallets->findByProviderCustomerId($providerCustomerId);
        if (! $wallet || $wallet->id() === null) {
            return;
        }

        $card = $this->vault->retrievePaymentMethodCard($providerPaymentMethodId);

        $this->methods->upsertActiveCard(
            walletId: (int)$wallet->id(),
            provider: 'stripe',
            providerPaymentMethodId: $providerPaymentMethodId,
            brand: $card->brand,
            last4: $card->last4,
            expMonth: $card->expMonth,
            expYear: $card->expYear,
        );
    }

    private function safeReserve(HandlePaymentWebhookInput $input): bool|null
    {
        try {
            return $this->webhookEvents->reserve(
                $input->provider,
                $input->eventId,
                $input->eventType,
                $input->payloadHash
            );
        } catch (\Throwable) {
            return null;
        }
    }

    private function safeComplete(
        HandlePaymentWebhookInput $input,
        string $status,
        ?int $paymentId,
        ?int $orderId,
        ?string $errorMessage,
    ): void {
        try {
            $this->webhookEvents->complete(
                $input->provider,
                $input->eventId,
                $status,
                $paymentId,
                $orderId,
                $errorMessage,
            );
        } catch (\Throwable) {
            // swallow
        }
    }
}
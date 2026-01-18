<?php

namespace App\Modules\Payment\Domain\Port;

use App\Modules\Payment\Domain\Enum\PaymentMethod;

interface PaymentGatewayPort
{
    public function createIntent(
        PaymentMethod $method,
        int $amount,
        string $currency,
        array $context
    ): array;

    /**
     * ✅ v1-4: OneClick（保存カードで決済）
     *
     * Return keys:
     * - provider_payment_id (string)
     * - client_secret (string|null)
     * - requires_action (bool)
     * - status (string|null)
     */
    public function createOneClickIntent(
        string $providerCustomerId,
        string $providerPaymentMethodId,
        int $amount,
        string $currency,
        array $context
    ): array;
}
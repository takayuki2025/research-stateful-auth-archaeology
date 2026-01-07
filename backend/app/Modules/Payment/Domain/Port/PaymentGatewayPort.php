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

    public function handleWebhook(string $payload, string $signature): array;

    public function createPaymentIntent(
        PaymentMethod $method,
        array $payload,
    ): array;
}

<?php

namespace App\Modules\Payment\Domain\Port;

use App\Modules\Payment\Domain\Enum\PaymentMethod;

interface PaymentGatewayPort
{
    /**
     * Create PaymentIntent (and possibly confirm for konbini)
     *
     * Return keys:
     * - provider_payment_id (string)
     * - client_secret (string|null)
     * - requires_action (bool)
     * - status (string|null)
     * - instructions (array|null)
     */
    public function createIntent(
        PaymentMethod $method,
        int $amount,
        string $currency,
        array $context
    ): array;
}
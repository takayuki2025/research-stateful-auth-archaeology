<?php

namespace Tests\Mocks;

use App\Modules\Payment\Domain\Enum\PaymentMethod;
use App\Modules\Payment\Domain\Port\PaymentGatewayPort;

final class FakePaymentGateway implements PaymentGatewayPort
{
    public function start(
        PaymentMethod $method,
        int $amount,
        string $currency,
        array $context
    ): array {
        return [
            'provider_payment_id' => 'pi_test_123',
            'client_secret' => 'cs_test_123',
            'requires_action' => false,
            'status' => 'requires_payment_method',
        ];
    }

    public function createPaymentIntent(
        PaymentMethod $method,
        array $payload
    ): array {
        return [
            'payment_intent_id' => 'pi_test_123',
            'client_secret' => 'secret_test_123',
        ];
    }

    public function parseWebhook(
        string $payload,
        string $signature
    ): array {
        return [];
    }
}

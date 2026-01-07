<?php

namespace App\Modules\Payment\Application\Dto;

final class StartPaymentOutput
{
    public function __construct(
        public readonly int $paymentId,
        public readonly string $status,
        public readonly ?string $providerPaymentId,
        public readonly ?string $clientSecret,
        public readonly ?array $instructions,
    ) {
    }

    public function toArray(): array
    {
        return [
            'payment_id' => $this->paymentId,
            'status' => $this->status,
            'provider_payment_id' => $this->providerPaymentId,
            'client_secret' => $this->clientSecret,
            'instructions' => $this->instructions,
        ];
    }
}

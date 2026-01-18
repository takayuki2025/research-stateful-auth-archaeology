<?php

namespace App\Modules\Payment\Application\Dto\Wallet;

final class OneClickCheckoutOutput
{
    public function __construct(
        public int $payment_id,
        public string $status,
        public string $provider_payment_id,
        public ?string $client_secret,
        public bool $requires_action,
    ) {
    }

    public function toArray(): array
    {
        return [
            'payment_id' => $this->payment_id,
            'status' => $this->status,
            'provider_payment_id' => $this->provider_payment_id,
            'client_secret' => $this->client_secret,
            'requires_action' => $this->requires_action,
        ];
    }
}
<?php

namespace App\Modules\Payment\Application\Dto\Wallet;

final class CreateSetupIntentOutput
{
    public function __construct(
        public string $setup_intent_id,
        public string $client_secret,
    ) {
    }

    public function toArray(): array
    {
        return [
            'setup_intent_id' => $this->setup_intent_id,
            'client_secret' => $this->client_secret,
        ];
    }
}
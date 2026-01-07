<?php

namespace App\Modules\Payment\Application\Dto;

final class PaymentInstructions
{
    public function __construct(
        public readonly ?string $type = null,        // konbini, bank_transfer...
        public readonly ?string $reference = null,   // dummy reference
        public readonly ?string $expiresAt = null,   // ISO8601
        public readonly ?string $payload = null,     // QR/Barcode base64 etc (future)
    ) {
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'reference' => $this->reference,
            'expires_at' => $this->expiresAt,
            'payload' => $this->payload,
        ];
    }
}

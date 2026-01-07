<?php

namespace App\Modules\Payment\Application\Dto;

final class HandlePaymentWebhookInput
{
    public function __construct(
        public readonly string $provider,
        public readonly string $eventId,
        public readonly string $eventType,
        public readonly array  $payload,
        public readonly string $payloadHash,
        public readonly \DateTimeImmutable $occurredAt,
    ) {
    }
}

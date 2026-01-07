<?php

namespace App\Modules\Payment\Domain\Event;

final class DomainPaymentEvent
{
    /**
     * @param array|null $instructions
     * 例:
     * [
     *   'type' => 'konbini',
     *   'expires_at' => 1766669999,
     *   'store' => [
     *      'lawson' => ['confirmation_number' => '1234567890'],
     *      ...
     *   ]
     * ]
     */
    public function __construct(
        public readonly DomainPaymentEventType $type,
        public readonly string $providerPaymentId,
        public readonly ?string $reason,
        public readonly \DateTimeImmutable $occurredAt,
        public readonly ?array $instructions = null,
    ) {
    }

    public static function ignored(\DateTimeImmutable $occurredAt): self
    {
        return new self(
            DomainPaymentEventType::IGNORED,
            '__ignored__',
            null,
            $occurredAt,
            null,
        );
    }
}

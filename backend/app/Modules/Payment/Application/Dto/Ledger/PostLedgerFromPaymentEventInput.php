<?php

namespace App\Modules\Payment\Application\Dto\Ledger;

final class PostLedgerFromPaymentEventInput
{
    public function __construct(
        public string $sourceProvider,     // stripe
        public string $sourceEventId,      // evt_xxx
        public int $shopId,
        public ?int $orderId,
        public ?int $paymentId,
        public string $postingType,        // sale/refund
        public int $amount,                // positive
        public string $currency,
        public \DateTimeImmutable $occurredAt,
        public ?array $meta = null,
    ) {
    }
}

<?php

namespace App\Modules\Shop\Domain\Entity;

use App\Modules\Shop\Domain\Enum\LedgerType;

final class ShopLedger
{
    public function __construct(
        public readonly int $shopId,
        public readonly LedgerType $type,
        public readonly int $amount,
        public readonly string $currency,
        public readonly ?int $orderId,
        public readonly ?int $paymentId,
        public readonly ?array $meta,
        public readonly \DateTimeImmutable $occurredAt,
    ) {
    }
}

<?php

namespace App\Modules\Payment\Domain\Ledger\Repository;

interface LedgerPostingRepository
{
    /**
     * 冪等で posting を確保する。
     * すでに存在する場合は null を返す（=既処理）。
     */
    public function reserve(
        string $sourceProvider,
        string $sourceEventId,
        int $shopId,
        ?int $orderId,
        ?int $paymentId,
        string $postingType,
        int $amount,
        string $currency,
        \DateTimeImmutable $occurredAt,
        ?array $meta = null,
    ): ?int;
}
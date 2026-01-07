<?php

namespace App\Modules\Shop\Domain\Repository;

interface ShopLedgerRepository
{
    public function recordSale(
        int $shopId,
        int $amount,
        string $currency,
        int $orderId,
        int $paymentId,
        \DateTimeImmutable $occurredAt,
    ): void;

    public function recordRefund(
        int $shopId,
        int $amount,
        string $currency,
        int $orderId,
        int $paymentId,
        string $provider,
        string $providerRefundId,
        ?string $reason,
        \DateTimeImmutable $occurredAt,
    ): void;

    /**
     * 冪等性チェック用（同じ refund を二重記録しない）
     */
    public function existsRefundByProviderRefundId(
        string $provider,
        string $providerRefundId,
    ): bool;
}

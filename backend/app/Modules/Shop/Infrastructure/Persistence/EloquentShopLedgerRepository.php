<?php

namespace App\Modules\Shop\Infrastructure\Persistence;

use App\Models\ShopLedger as EloquentShopLedger;
use App\Modules\Shop\Domain\Repository\ShopLedgerRepository;
use App\Modules\Shop\Domain\Enum\LedgerType;

final class EloquentShopLedgerRepository implements ShopLedgerRepository
{
    public function recordSale(
        int $shopId,
        int $amount,
        string $currency,
        int $orderId,
        int $paymentId,
        \DateTimeImmutable $occurredAt,
    ): void {
        EloquentShopLedger::create([
            'shop_id'     => $shopId,
            'type'        => LedgerType::SALE->value,
            'amount'      => $amount,
            'currency'    => $currency,
            'order_id'    => $orderId,
            'payment_id'  => $paymentId,
            'meta'        => null,
            'occurred_at' => $occurredAt,
        ]);
    }

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
    ): void {
        EloquentShopLedger::create([
            'shop_id'     => $shopId,
            'type'        => LedgerType::REFUND->value,
            'amount'      => -abs($amount), // 🔴 必ずマイナス
            'currency'    => $currency,
            'order_id'    => $orderId,
            'payment_id'  => $paymentId,
            'meta'        => [
                'provider'            => $provider,
                'provider_refund_id'  => $providerRefundId,
                'reason'              => $reason,
            ],
            'occurred_at' => $occurredAt,
        ]);
    }

    public function existsRefundByProviderRefundId(
        string $provider,
        string $providerRefundId,
    ): bool {
        return EloquentShopLedger::query()
            ->where('type', LedgerType::REFUND->value)
            ->where('meta->provider', $provider)
            ->where('meta->provider_refund_id', $providerRefundId)
            ->exists();
    }
}

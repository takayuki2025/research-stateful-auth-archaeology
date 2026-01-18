<?php

namespace App\Modules\Shop\Infrastructure\Persistence;

use App\Models\ShopLedger as EloquentShopLedger;
use App\Modules\Shop\Domain\Repository\ShopLedgerRepository;
use App\Modules\Shop\Domain\Enum\LedgerType;
use Illuminate\Support\Facades\DB;

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
        // ✅ UNIQUE(type, order_id, payment_id) に当たっても例外で落とさない
        DB::table('shop_ledgers')->insertOrIgnore([
            'shop_id'     => $shopId,
            'type'        => LedgerType::SALE->value,
            'amount'      => $amount,
            'currency'    => $currency,
            'order_id'    => $orderId,
            'payment_id'  => $paymentId,
            'meta'        => null,
            'occurred_at' => $occurredAt->format('Y-m-d H:i:s'),
            'created_at'  => now(),
            'updated_at'  => now(),
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
        // refund は既に existsRefundByProviderRefundId で冪等化しているが、
        // ここも insertOrIgnore にしておくと「例外で落ちない」ので安全
        DB::table('shop_ledgers')->insertOrIgnore([
            'shop_id'     => $shopId,
            'type'        => LedgerType::REFUND->value,
            'amount'      => -abs($amount),
            'currency'    => $currency,
            'order_id'    => $orderId,
            'payment_id'  => $paymentId,
            'meta'        => json_encode([
                'provider'           => $provider,
                'provider_refund_id' => $providerRefundId,
                'reason'             => $reason,
            ], JSON_UNESCAPED_UNICODE),
            'occurred_at' => $occurredAt->format('Y-m-d H:i:s'),
            'created_at'  => now(),
            'updated_at'  => now(),
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
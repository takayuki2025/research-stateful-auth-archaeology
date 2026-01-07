<?php

namespace App\Modules\Order\Infrastructure\EventListener;

use App\Modules\Order\Domain\Event\OrderPaid;
use Illuminate\Support\Facades\DB;

/**
 * OrderPaid を受けて購入履歴（Query 用）を記録する
 *
 * - Source of Truth: orders.items_snapshot
 * - order_items は使用しない（v1 方針A）
 * - 冪等性は v2 以降で対応
 */
final class OnOrderPaidRecordOrderHistory
{
    public function handle(OrderPaid $event): void
    {

        // =========================
        // Order 取得
        // =========================
        $order = DB::table('orders')
            ->where('id', $event->orderId)
            ->first();

        if (! $order) {
            \Log::warning('[OrderHistory] order not found', [
                'order_id' => $event->orderId,
            ]);
            return;
        }

        // =========================
        // items_snapshot 展開
        // =========================
        $items = json_decode($order->items_snapshot, true);

        if (! is_array($items) || count($items) === 0) {
            \Log::warning('[OrderHistory] items_snapshot empty', [
                'order_id' => $event->orderId,
            ]);
            return;
        }

        // =========================
        // order_histories INSERT
        // =========================
        foreach ($items as $item) {
            DB::table('order_histories')->insert([
                'shop_id'        => $order->shop_id,
                'order_id'       => $order->id,
                'item_id'        => $item['item_id'],
                'user_id'        => $order->user_id,

                // 表示用スナップショット
                'item_name'      => $item['name'],
                'item_image'     => $item['image_path'] ?? null,
                'price_amount'   => $item['price_amount'],
                'price_currency' => $item['price_currency'],
                'quantity'       => $item['quantity'] ?? 1,

                // v1 固定
                'payment_method' => 'stripe',

                // Address Snapshot
                'buy_address'    => json_encode(
                    $order->address_snapshot,
                    JSON_UNESCAPED_UNICODE
                ),

                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }
}

<?php

namespace App\Modules\Order\Infrastructure\EventListener;

use App\Modules\Order\Domain\Event\OrderPaid;
use Illuminate\Support\Facades\DB;

final class OnOrderPaidRecordOrderHistory
{
    public function handle(OrderPaid $event): void
    {
        $order = DB::table('orders')
            ->where('id', $event->orderId)
            ->first();

        if (! $order) {
            \Log::warning('[OrderHistory] order not found', [
                'order_id' => $event->orderId,
            ]);
            return;
        }

        $items = json_decode($order->items_snapshot, true);

        if (! is_array($items) || count($items) === 0) {
            \Log::warning('[OrderHistory] items_snapshot empty', [
                'order_id' => $event->orderId,
            ]);
            return;
        }

        foreach ($items as $item) {
            DB::table('order_histories')->insertOrIgnore([
                'shop_id'        => $order->shop_id,
                'order_id'       => $order->id,
                'item_id'        => $item['item_id'],
                'user_id'        => $order->user_id,

                'item_name'      => $item['name'],
                'item_image'     => $item['image_path'] ?? null,
                'price_amount'   => $item['price_amount'],
                'price_currency' => $item['price_currency'],
                'quantity'       => $item['quantity'] ?? 1,

                // v1 固定（必要なら card/konbini を入れるよう拡張可）
                'payment_method' => 'stripe',

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
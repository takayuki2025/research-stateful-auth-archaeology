<?php

namespace App\Modules\Order\Application\UseCase;

use App\Modules\Order\Application\Dto\CreateOrderInput;
use App\Modules\Order\Application\Dto\CreateOrderOutput;
use App\Modules\Order\Application\Dto\OrderItemSnapshot;
use App\Modules\Order\Domain\Entity\Order;
use App\Modules\Order\Domain\Repository\OrderRepository;
use App\Modules\Order\Domain\Repository\OrderHistoryRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateOrderUseCase
{
    public function __construct(
        private OrderRepository $orders,
        private OrderHistoryRepository $history,
    ) {
    }

    public function handle(CreateOrderInput $input): CreateOrderOutput
    {
        return DB::transaction(function () use ($input) {

            // ① OrderItemSnapshot を構築
            $snapshots = array_map(function (array $row) {
                return OrderItemSnapshot::fromArray([
                    'item_id'        => $row['item_id'],
                    'name'           => $row['name'],
                    'price_amount'   => $row['price_amount'],
                    'price_currency' => $row['price_currency'],
                    'quantity'       => $row['quantity'] ?? 1,
                    'image_path'     => $row['image_path'] ?? null,
                ]);
            }, $input->items);

            // ② 金額計算
            $currency = $snapshots[0]->priceCurrency;
            $totalAmount = 0;

            foreach ($snapshots as $s) {
                if ($s->priceCurrency !== $currency) {
                    throw new \DomainException('Mixed currency not supported');
                }
                $totalAmount += $s->priceAmount * $s->quantity;
            }

            // ✅ ③ order_number 発番（衝突時リトライ）
            $orderNumber = $this->generateOrderNumber();

            // ④ Order 新規作成
            $order = Order::create(
                orderNumber: $orderNumber,
                shopId: $input->shopId,
                userId: $input->userId,
                totalAmount: $totalAmount,
                currency: $currency,
                items: $snapshots,
                meta: $input->meta
            );

            $saved = $this->orders->save($order);

            // ⑤ order_items を作成（あなたの既存）
            foreach ($snapshots as $snapshot) {
                DB::table('order_items')->insert([
                    'order_id'       => $saved->id(),
                    'item_id'        => $snapshot->itemId,
                    'shop_id'        => $saved->shopId(),
                    'item_name'      => $snapshot->name,
                    'item_image'     => $snapshot->imagePath,
                    'price_amount'   => $snapshot->priceAmount,
                    'price_currency' => $snapshot->priceCurrency,
                    'quantity'       => $snapshot->quantity,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            // ⑥ OrderHistory
            $this->history->addEvent(
                orderId: $saved->id(),
                type: 'created',
                payload: [
                    'shop_id'      => $saved->shopId(),
                    'user_id'      => $saved->userId(),
                    'total_amount' => $saved->totalAmount(),
                    'currency'     => $saved->currency(),
                    'order_number' => $saved->orderNumber(), // ✅ 監査に効く
                ]
            );

            return CreateOrderOutput::from(
                orderId: $saved->id(),
                status: $saved->status(),
                totalAmount: $saved->totalAmount(),
                currency: $saved->currency()
            );
        });
    }

    private function generateOrderNumber(): string
    {
        // ORD-20260118-8C2A9FQ1 のような形式
        $date = now()->format('Ymd');

        // 衝突は稀だがゼロではないので短いリトライを入れる
        for ($i = 0; $i < 5; $i++) {
            $rand = Str::upper(Str::random(8));
            return "ORD-{$date}-{$rand}";
        }

        // ここには基本来ない（念のため）
        return "ORD-{$date}-" . Str::upper(Str::random(12));
    }
}
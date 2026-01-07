<?php

namespace App\Modules\Order\Infrastructure\Persistence;

use App\Modules\Order\Domain\Entity\Order;
use App\Modules\Order\Domain\Enum\OrderStatus;
use App\Modules\Order\Domain\Repository\OrderRepository;
use App\Modules\Order\Domain\ValueObject\Address;
use App\Modules\Order\Application\Dto\OrderItemSnapshot;
use App\Modules\Order\Infrastructure\Persistence\Models\OrderModel;

final class EloquentOrderRepository implements OrderRepository
{
    public function findById(int $orderId): ?Order
    {
        $model = OrderModel::find($orderId);
        return $model ? $this->reconstituteOrder($model) : null;
    }

    public function findDraftByUser(int $orderId, int $userId): Order
    {
        $model = OrderModel::query()
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->where('status', OrderStatus::PENDING_PAYMENT->value)
            ->firstOrFail();

        return $this->reconstituteOrder($model);
    }

    public function findDraftByUserAndShop(int $userId, int $shopId): ?Order
    {
        $model = OrderModel::query()
            ->where('user_id', $userId)
            ->where('shop_id', $shopId)
            ->where('status', OrderStatus::PENDING_PAYMENT->value)
            ->orderByDesc('id')
            ->first();

        return $model ? $this->reconstituteOrder($model) : null;
    }

    public function save(Order $order): Order
    {
        $model = $order->id()
            ? OrderModel::findOrFail($order->id())
            : new OrderModel();

        $model->shop_id = $order->shopId();
        $model->user_id = $order->userId();
        $model->status = $order->status()->value;
        $model->total_amount = $order->totalAmount();
        $model->currency = $order->currency();

        $model->items_snapshot = array_map(
            fn (OrderItemSnapshot $item) => $item->toArray(),
            $order->items()
        );

        $model->meta = $order->meta();

        // Address snapshot（nullable）

        $address = $order->shippingAddress();

        if ($address !== null) {
            $model->address_snapshot = $address->toArray();
            $model->address_confirmed_at = $order->addressSnapshotAt();
        } else {
            $model->address_snapshot = null;
            $model->address_confirmed_at = null;
        }


        $model->save();

        return $this->reconstituteOrder($model);
    }

    /** @return Order[] */
    public function findByBuyer(int $userId): array
    {
        return OrderModel::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->get()
            ->map(fn (OrderModel $m) => $this->reconstituteOrder($m))
            ->all();
    }

    /** @return Order[] */
    public function findByShop(int $shopId): array
    {
        return OrderModel::query()
            ->where('shop_id', $shopId)
            ->orderByDesc('id')
            ->get()
            ->map(fn (OrderModel $m) => $this->reconstituteOrder($m))
            ->all();
    }

    // =====================================
    // Reconstitution（Aggregate 復元）
    // =====================================
    private function reconstituteOrder(OrderModel $model): Order
    {
        $items = array_map(
            fn (array $row) => OrderItemSnapshot::fromArray($row),
            $model->items_snapshot
        );

        $address = null;
        $confirmedAt = null;

        if ($model->address_snapshot) {
            $address = Address::fromArray($model->address_snapshot);
            $confirmedAt = $model->address_confirmed_at?->toDateTimeImmutable();
        }

        return Order::reconstitute(
            id: $model->id,
            shopId: $model->shop_id,
            userId: $model->user_id,
            status: OrderStatus::from($model->status),
            totalAmount: $model->total_amount,
            currency: $model->currency,
            items: $items,
            meta: $model->meta,
            shippingAddress: $address,
            addressSnapshotAt: $confirmedAt
        );
    }
}

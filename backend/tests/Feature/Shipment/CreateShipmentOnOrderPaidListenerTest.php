<?php

namespace Tests\Unit\Shipment;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Order\Domain\Entity\Order;
use App\Modules\Order\Application\Dto\OrderItemSnapshot;
use App\Modules\Order\Domain\ValueObject\Address;
use App\Modules\Order\Domain\Event\OrderPaid;
use App\Modules\Order\Domain\Repository\OrderRepository;
use App\Modules\Shipment\Domain\Repository\ShipmentRepository;
use App\Modules\Shipment\Application\Listener\CreateShipmentOnOrderPaidListener;

final class CreateShipmentOnOrderPaidListenerTest extends TestCase
{
    use RefreshDatabase;

    private function createOrder(bool $withAddress): Order
    {
        $orders = app(OrderRepository::class);

        $order = Order::create(
            shopId: 1,
            userId: 1,
            totalAmount: 2000,
            currency: 'JPY',
            items: [
                OrderItemSnapshot::fromArray([
                    'item_id' => 1,
                    'name' => 'Test Item',
                    'price_amount' => 2000,
                    'price_currency' => 'JPY',
                    'condition' => 'new',
                    'category' => ['test'],
                    'image_path' => null,
                ])
            ]
        );

        if ($withAddress) {
            $order->confirmAddress(
                new Address(
                    postalCode: '100-0001',
                    prefecture: '東京都',
                    city: '千代田区',
                    addressLine1: '千代田1-1',
                    addressLine2: null,
                    recipientName: '山田 太郎',
                    phone: '09000000000',
                ),
                now()->toDateTimeImmutable()
            );
        }

        return $orders->save($order);
    }

    public function test_it_creates_shipment_when_order_is_paid_and_has_address(): void
    {
        $order = $this->createOrder(true);

        $listener = app(CreateShipmentOnOrderPaidListener::class);

        $listener->handle(
            new OrderPaid(
                orderId: $order->id(),
                shopId: $order->shopId()
            )
        );

        $this->assertDatabaseHas('shipments', [
            'order_id' => $order->id(),
            'shop_id'  => $order->shopId(),
        ]);
    }

    public function test_it_does_not_create_shipment_if_address_is_missing(): void
    {
        $order = $this->createOrder(false);

        $listener = app(CreateShipmentOnOrderPaidListener::class);

        $listener->handle(
            new OrderPaid(
                orderId: $order->id(),
                shopId: $order->shopId()
            )
        );

        $this->assertDatabaseMissing('shipments', [
            'order_id' => $order->id(),
        ]);
    }

    public function test_it_is_idempotent_and_creates_only_one_shipment(): void
    {
        $order = $this->createOrder(true);

        $listener = app(CreateShipmentOnOrderPaidListener::class);

        $event = new OrderPaid(
            orderId: $order->id(),
            shopId: $order->shopId()
        );

        $listener->handle($event);
        $listener->handle($event); // 冪等

        $this->assertSame(
            1,
            app(ShipmentRepository::class)
                ->existsByOrderId($order->id())
                ? 1
                : 0
        );

        $this->assertSame(
            1,
            \DB::table('shipments')
                ->where('order_id', $order->id())
                ->count()
        );
    }
}

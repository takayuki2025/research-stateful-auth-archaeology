<?php

namespace Tests\Feature\Payment;

use App\Models\User;
use App\Modules\Order\Domain\Entity\Order;
use App\Modules\Order\Domain\ValueObject\Address;
use App\Modules\Order\Application\Dto\OrderItemSnapshot;
use App\Modules\Payment\Domain\Enum\PaymentMethod;
use App\Modules\Payment\Application\Dto\CreatePaymentIntentInput;
use App\Modules\Payment\Application\UseCase\CreatePaymentIntentUseCase;
use App\Modules\Order\Domain\Repository\OrderRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Mocks\FakePaymentGateway;
use Tests\TestCase;
use DomainException;

final class CreatePaymentIntentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(
            \App\Modules\Payment\Domain\Port\PaymentGatewayPort::class,
            FakePaymentGateway::class
        );
    }

    private function makeOrderItems(): array
    {
        return [
            OrderItemSnapshot::fromArray([
                'item_id' => 1,
                'name' => 'Test Item',

                // ★ 必須
                'price_amount' => 5000,
                'price_currency' => 'JPY',

                // ★ 任意（DTO が期待する形に合わせる）
                'condition' => 'new',
                'category' => ['test'],
                'image_path' => null,
            ]),
        ];
    }

    public function test_payment_intent_cannot_be_created_without_shipping_address(): void
    {
        $user = User::factory()->create();

        $order = Order::create(
            shopId: 1,
            userId: $user->id,
            totalAmount: 5000,
            currency: 'JPY',
            items: $this->makeOrderItems(),
        );

        $orders = $this->app->make(OrderRepository::class);

        $order = $orders->save($order);


        $useCase = $this->app->make(CreatePaymentIntentUseCase::class);

        $input = new CreatePaymentIntentInput(
            orderId: $order->id(),
            userId: $user->id,
            method: PaymentMethod::CARD
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(
            'Shipping address must be confirmed before payment.'
        );

        $useCase->handle($input);
    }

    public function test_payment_intent_is_created_when_shipping_address_is_confirmed(): void
    {
        $user = User::factory()->create();

        $order = Order::create(
            shopId: 1,
            userId: $user->id,
            totalAmount: 5000,
            currency: 'JPY',
            items: $this->makeOrderItems(),
        );

        $address = new Address(
            postalCode: '100-0001',
            prefecture: '東京都',
            city: '千代田区',
            addressLine1: '千代田1-1',
            addressLine2: null,
            recipientName: '山田 太郎',
            phone: '09000000000',
        );

        $order->confirmAddress($address, now()->toDateTimeImmutable());

        $orders = $this->app->make(OrderRepository::class);

        $order = $orders->save($order);


        $useCase = $this->app->make(CreatePaymentIntentUseCase::class);

        $input = new CreatePaymentIntentInput(
            orderId: $order->id(),
            userId: $user->id,
            method: PaymentMethod::CARD
        );

        $result = $useCase->handle($input);

        $this->assertSame('pi_test_123', $result['payment_intent_id']);
        $this->assertArrayHasKey('client_secret', $result);
    }
}

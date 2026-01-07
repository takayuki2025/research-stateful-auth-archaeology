<?php

namespace App\Modules\Payment\Application\UseCase;

use App\Modules\Order\Domain\Repository\OrderRepository;
use App\Modules\Payment\Application\Dto\CreatePaymentIntentInput;
use App\Modules\Payment\Domain\Enum\PaymentMethod;
use App\Modules\Payment\Domain\Port\PaymentGatewayPort;
use DomainException;

final class CreatePaymentIntentUseCase
{
    public function __construct(
        private OrderRepository $orders,
        private PaymentGatewayPort $gateway,
    ) {
    }

    public function handle(CreatePaymentIntentInput $input): array
    {
        $order = $this->orders->findDraftByUser(
            orderId: $input->orderId,
            userId: $input->userId,
        );

        // =========================
        // Guard: Address 必須
        // =========================
        if ($order->shippingAddress() === null) {
            throw new DomainException('Shipping address must be confirmed before payment.');
        }

        // =========================
        // Payload 構築
        // =========================
        $address = $order->shippingAddress()->toArray();

        $payload = [
            'amount' => $order->totalAmount(),
            'currency' => $order->currency(),
            'metadata' => [
                'order_id' => (string) $order->id(),
                'shop_id' => (string) $order->shopId(),
                'user_id' => (string) $order->userId(),
            ],
            'shipping' => [
                'name' => $address['recipient_name'] ?? '',
                'phone' => $address['phone'] ?? '',
                'address' => [
                    'postal_code' => $address['postal_code'],
                    'state' => $address['prefecture'],
                    'city' => $address['city'],
                    'line1' => $address['address_line1'],
                    'line2' => $address['address_line2'],
                    'country' => 'JP',
                ],
            ],
        ];

        return $this->gateway->createPaymentIntent(
            method: $input->method,
            payload: $payload,
        );
    }
}

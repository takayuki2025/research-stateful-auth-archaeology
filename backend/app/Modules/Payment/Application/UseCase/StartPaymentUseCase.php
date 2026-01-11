<?php

namespace App\Modules\Payment\Application\UseCase;

use App\Modules\Order\Domain\Repository\OrderRepository;
use App\Modules\Order\Domain\Enum\OrderStatus;
use App\Modules\Payment\Application\Dto\StartPaymentInput;
use App\Modules\Payment\Application\Dto\StartPaymentOutput;
use App\Modules\Payment\Domain\Entity\Payment;
use App\Modules\Payment\Domain\Enum\PaymentMethod;
use App\Modules\Payment\Domain\Enum\PaymentProvider;
use App\Modules\Payment\Domain\Repository\PaymentRepository;
use App\Modules\Payment\Domain\Port\PaymentGatewayPort;
use Illuminate\Support\Facades\DB;

final class StartPaymentUseCase
{
    public function __construct(
        private OrderRepository $orders,
        private PaymentRepository $payments,
        private PaymentGatewayPort $gateway,
    ) {
    }

    public function handle(StartPaymentInput $input, int $userId): StartPaymentOutput
    {
        return DB::transaction(function () use ($input, $userId) {

            /* ============================================
               ① Order 検証
            ============================================ */
            $order = $this->orders->findById($input->orderId);
            if (! $order) {
                throw new \RuntimeException('Order not found');
            }

            if ((int) $order->userId() !== $userId) {
                throw new \DomainException('Forbidden');
            }

            if ($order->status() !== OrderStatus::PENDING_PAYMENT) {
                throw new \DomainException('Order is not payable');
            }

            if ($order->shippingAddress() === null) {
                throw new \DomainException('Shipping address must be confirmed before payment.');
            }

            $method = PaymentMethod::from($input->method);

            /* ============================================
               ② Payment を先に作成（id を確定）
            ============================================ */
            $payment = Payment::initiate(
                orderId: $order->id(),
                shopId: $order->shopId(),
                userId: $order->userId(),
                provider: PaymentProvider::STRIPE,
                method: $method,
                amount: $order->totalAmount(),
                currency: $order->currency(),
            );

            $payment = $this->payments->save($payment);

            /* ============================================
               ③ Stripe PaymentIntent 作成
            ============================================ */
            $res = $this->gateway->createIntent(
                method: $method,
                amount: $order->totalAmount(),
                currency: $order->currency(),
                context: [
                    'order_id'   => $order->id(),
                    'payment_id' => $payment->id(), // ★最重要
                    'user_id'    => $order->userId(),
                    'shop_id'    => $order->shopId(),
                    'payer_name' => '購入者-' . $order->userId(),
                ]
            );

            if (empty($res['provider_payment_id'])) {
                throw new \RuntimeException('provider_payment_id missing from gateway response');
            }

            /* ============================================
               ④ Payment 更新
            ============================================ */
            $payment = $payment->withProviderPayment($res['provider_payment_id']);

            if (($res['requires_action'] ?? false) === true) {
                $payment = $payment->markRequiresAction([
                    'gateway_status' => $res['status'] ?? null,
                ]);
            }

            if (! empty($res['instructions'])) {
                $payment = $payment->withInstructions($res['instructions']);
            }

            $payment = $this->payments->save($payment);

            /* ============================================
               ⑤ レスポンス
            ============================================ */
            return new StartPaymentOutput(
                paymentId: $payment->id(),
                status: $payment->status()->value,
                providerPaymentId: $payment->providerPaymentId(),
                clientSecret: $res['client_secret'] ?? null,
                instructions: $res['instructions'] ?? null
            );
        });
    }
}
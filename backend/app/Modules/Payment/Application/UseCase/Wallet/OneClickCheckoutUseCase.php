<?php

namespace App\Modules\Payment\Application\UseCase\Wallet;

use App\Modules\Order\Domain\Enum\OrderStatus;
use App\Modules\Order\Domain\Repository\OrderRepository;
use App\Modules\Payment\Application\Dto\Wallet\OneClickCheckoutOutput;
use App\Modules\Payment\Domain\Entity\Payment;
use App\Modules\Payment\Domain\Enum\PaymentMethod;
use App\Modules\Payment\Domain\Enum\PaymentProvider;
use App\Modules\Payment\Domain\Repository\PaymentRepository;
use App\Modules\Payment\Domain\Port\PaymentGatewayPort;
use App\Modules\Payment\Domain\Repository\Wallet\WalletRepository;
use App\Modules\Payment\Domain\Repository\Wallet\StoredPaymentMethodRepository;
use Illuminate\Support\Facades\DB;

final class OneClickCheckoutUseCase
{
    public function __construct(
        private OrderRepository $orders,
        private PaymentRepository $payments,
        private PaymentGatewayPort $gateway,
        private WalletRepository $wallets,
        private StoredPaymentMethodRepository $methods,
    ) {
    }

    public function handle(int $userId, int $orderId, ?int $storedPaymentMethodId = null): OneClickCheckoutOutput
    {
        return DB::transaction(function () use ($userId, $orderId, $storedPaymentMethodId) {

            // ① Order検証
            $order = $this->orders->findById($orderId);
            if (! $order) {
                throw new \RuntimeException('Order not found');
            }
            if ((int)$order->userId() !== $userId) {
                throw new \DomainException('Forbidden');
            }
            if ($order->status() !== OrderStatus::PENDING_PAYMENT) {
                throw new \DomainException('Order is not payable');
            }
            if ($order->shippingAddress() === null) {
                throw new \DomainException('Shipping address must be confirmed before payment.');
            }

            // ② Wallet/Default PaymentMethod取得
            $wallet = $this->wallets->findByUserId($userId);
            if (! $wallet || $wallet->id() === null) {
                throw new \DomainException('Wallet not found');
            }
            $providerCustomerId = $wallet->providerCustomerId();
            if (!is_string($providerCustomerId) || $providerCustomerId === '') {
                throw new \DomainException('Saved card customer is not ready');
            }

            $pmRow = null;

            if (is_int($storedPaymentMethodId)) {
                $pmRow = $this->methods->findRowById($storedPaymentMethodId);
                if (! $pmRow) {
                    throw new \DomainException('Payment method not found');
                }
                if ((int)$pmRow['wallet_id'] !== (int)$wallet->id()) {
                    throw new \DomainException('Forbidden');
                }
            } else {
                $pmRow = $this->methods->findDefaultActiveRow((int)$wallet->id());
                if (! $pmRow) {
                    throw new \DomainException('Default payment method not found');
                }
            }

            if (($pmRow['status'] ?? '') !== 'active') {
                throw new \DomainException('Payment method is not active');
            }

            // v1 policy：card のみ OneClick可
            $source = (string)($pmRow['source'] ?? 'card');
            if ($source !== 'card') {
                throw new \DomainException('This payment method is not eligible for one-click');
            }

            $providerPmId = (string)$pmRow['provider_payment_method_id'];

            // ③ Paymentを先に作成（payment_idをmetadataへ）
            $payment = Payment::initiate(
                orderId: $order->id(),
                shopId: $order->shopId(),
                userId: $order->userId(),
                provider: PaymentProvider::STRIPE,
                method: PaymentMethod::CARD,
                amount: $order->totalAmount(),
                currency: $order->currency(),
            );

            $payment = $this->payments->save($payment);

            // ④ Stripe OneClick PaymentIntent 作成
            $res = $this->gateway->createOneClickIntent(
                providerCustomerId: $providerCustomerId,
                providerPaymentMethodId: $providerPmId,
                amount: $order->totalAmount(),
                currency: $order->currency(),
                context: [
                    'order_id' => $order->id(),
                    'payment_id' => $payment->id(), // ✅ 重要（R3救済）
                    'user_id' => $order->userId(),
                    'shop_id' => $order->shopId(),
                ],
            );

            if (empty($res['provider_payment_id'])) {
                throw new \RuntimeException('provider_payment_id missing from gateway response');
            }

            // ⑤ Payment更新（requires_actionなら状態更新）
            $payment = $payment->withProviderPayment($res['provider_payment_id']);

            if (($res['requires_action'] ?? false) === true) {
                $payment = $payment->markRequiresAction([
                    'gateway_status' => $res['status'] ?? null,
                ]);
            }

            $payment = $this->payments->save($payment);

            return new OneClickCheckoutOutput(
                payment_id: (int)$payment->id(),
                status: $payment->status()->value,
                provider_payment_id: (string)$payment->providerPaymentId(),
                client_secret: $res['client_secret'] ?? null,
                requires_action: (bool)($res['requires_action'] ?? false),
            );
        });
    }
}
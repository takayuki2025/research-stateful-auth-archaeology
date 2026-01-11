<?php

namespace App\Modules\Payment\Application\UseCase;

use App\Modules\Payment\Application\Dto\HandlePaymentWebhookInput;
use App\Modules\Payment\Domain\Enum\PaymentStatus;
use App\Modules\Payment\Domain\Event\DomainPaymentEventType;
use App\Modules\Payment\Domain\Repository\PaymentQueryRepository;
use App\Modules\Payment\Domain\Repository\PaymentRepository;
use App\Modules\Payment\Domain\Service\StripeEventMapper;
use App\Modules\Order\Domain\Event\OrderPaid;
use App\Modules\Order\Domain\Repository\OrderRepository;
use App\Modules\Shop\Domain\Repository\ShopLedgerRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class HandlePaymentWebhookUseCase
{
    public function __construct(
        private PaymentQueryRepository $webhookEvents,
        private PaymentRepository $payments,
        private OrderRepository $orders,
        private ShopLedgerRepository $ledgers,
        private StripeEventMapper $mapper,
    ) {
    }

    public function handle(HandlePaymentWebhookInput $input): void
    {
        // ① Webhook 冪等（reserve できない=すでに処理済み or 異常）は何もしない
        $reserved = $this->safeReserve($input);
        if ($reserved !== true) {
            return;
        }

        $paymentId = null;
        $orderId   = null;
        $orderPaidEvent = null;

        try {
            $domainEvent = $this->mapper->map($input);

            if ($domainEvent->type === DomainPaymentEventType::IGNORED) {
                // 処理対象外でも complete は残す（観測性）
                $this->safeComplete($input, 'ignored', null, null, null);
                return;
            }

            // ★ここで "order_id" はメタから拾う（Payment が無いケースのため）
            $orderIdFromMeta = $this->extractOrderIdFromPayloadMeta($input);

            DB::transaction(function () use (
                $domainEvent,
                $orderIdFromMeta,
                &$paymentId,
                &$orderId,
                &$orderPaidEvent
            ) {
                // ----------------------------
                // ② Payment を探す（PI ID で）
                // ----------------------------
                $payment = $this->payments->findByProviderPaymentId($domainEvent->providerPaymentId);

                // ✅ Payment がまだ無い場合の救済（今回の本命）
                // - Stripe は succeeded 済み
                // - Payment insert より webhook が先に来ることがある
                // - この場合でも Order を paid に進める
                if (! $payment) {

                    // SUCCEEDED 以外なら何もしない（payment 未作成で失敗・要対応は拾えない）
                    if ($domainEvent->type !== DomainPaymentEventType::SUCCEEDED) {
                        return;
                    }

                    if (!is_int($orderIdFromMeta)) {
                        // order_id が取れない＝どの注文か分からないので触らない
                        return;
                    }

                    $order = $this->orders->findById($orderIdFromMeta);
                    if (! $order) {
                        return;
                    }

                    $orderId = $order->id();

                    // すでに paid なら何もしない
                    if ($order->isPaid()) {
                        return;
                    }

                    // ✅ Order を paid にする（Payment が無いので ledger/payment 更新はここではしない）
                    $paidOrder = $order->markPaid();
                    $this->orders->save($paidOrder);

                    $orderPaidEvent = new OrderPaid(
                        orderId: $paidOrder->id(),
                        shopId: $paidOrder->shopId(),
                    );

                    return;
                }

                // ここから先は Payment が存在する通常ルート
                $paymentId = $payment->id();
                $orderId   = $payment->orderId();

                // ----------------------------
                // ③ Stripe metadata と Payment.orderId の一致確認（安全装置）
                // ----------------------------
                if (is_int($orderIdFromMeta) && $orderIdFromMeta !== $payment->orderId()) {
                    // 別注文の Webhook が紐づく可能性 → 絶対に触らない
                    return;
                }

                // ----------------------------
                // ④ Refund
                // ----------------------------
                if ($domainEvent->type === DomainPaymentEventType::REFUND_SUCCEEDED) {

                    $meta = $domainEvent->instructions ?? [];
                    $refundId = $meta['provider_refund_id'] ?? null;

                    if (!is_string($refundId) || $refundId === '') {
                        return;
                    }

                    if ($this->ledgers->existsRefundByProviderRefundId('stripe', $refundId)) {
                        return; // 冪等
                    }

                    $this->ledgers->recordRefund(
                        shopId: $payment->shopId(),
                        amount: $payment->amount(),
                        currency: $payment->currency(),
                        orderId: $payment->orderId(),
                        paymentId: $payment->id(),
                        provider: 'stripe',
                        providerRefundId: $refundId,
                        reason: $meta['reason'] ?? null,
                        occurredAt: $domainEvent->occurredAt,
                    );

                    return;
                }

                // ----------------------------
                // ⑤ 通常フロー（Payment → Order）
                // ----------------------------

                // すでに succeeded なら冪等で何もしない
                if ($payment->status() === PaymentStatus::SUCCEEDED) {
                    return;
                }

                if ($domainEvent->type === DomainPaymentEventType::FAILED) {
                    $this->payments->save(
                        $payment->markFailed(['reason' => $domainEvent->reason])
                    );
                    return;
                }

                if ($domainEvent->type === DomainPaymentEventType::REQUIRES_ACTION) {
                    $this->payments->save(
                        $payment->markRequiresAction()
                    );
                    return;
                }

                if ($domainEvent->type === DomainPaymentEventType::SUCCEEDED) {

                    // ✅ Order を取得
                    $order = $this->orders->findById($payment->orderId());
                    if (! $order) {
                        return;
                    }

                    // ✅ Order がすでに paid なら何もしない（最重要）
                    if ($order->isPaid()) {
                        // ただし Payment 側が succeeded でないなら整合のため更新してよい
                        $this->payments->save($payment->markSucceeded());
                        return;
                    }

                    // ✅ Payment を SUCCEEDED
                    $payment = $payment->markSucceeded();
                    $this->payments->save($payment);

                    // ✅ Order を paid
                    $paidOrder = $order->markPaid();
                    $this->orders->save($paidOrder);

                    $orderPaidEvent = new OrderPaid(
                        orderId: $paidOrder->id(),
                        shopId: $paidOrder->shopId(),
                    );

                    // ✅ Ledger 記録（冪等は repository 側で担保されている前提）
                    $this->ledgers->recordSale(
                        shopId: $payment->shopId(),
                        amount: $payment->amount(),
                        currency: $payment->currency(),
                        orderId: $payment->orderId(),
                        paymentId: $payment->id(),
                        occurredAt: $domainEvent->occurredAt,
                    );

                    return;
                }

                // ここに来るのは基本的に無いが、念のため何もしない
            });

            // ✅ afterCommit で 1回だけ dispatch（OrderPaidEvent があれば）
            if ($orderPaidEvent) {
                DB::afterCommit(fn () => Event::dispatch($orderPaidEvent));
            }

        } catch (\Throwable $e) {
            // 例外は Stripe には返さないが、complete には error として残す
            $this->safeComplete($input, 'error', $paymentId, $orderId, $e->getMessage());
            throw $e; // Controller 側で swallow する設計ならここは throw してOK
        } finally {
            // 正常系（ignored / ok / error）は上の分岐で可能な限り complete 済みだが、
            // 万一ここまで来ていれば ok で閉じる
            $this->safeComplete($input, 'ok', $paymentId, $orderId, null);
        }
    }

    /**
     * Stripe payload 内の metadata.order_id を拾う（Payment 不在ケース救済のため）
     */
    private function extractOrderIdFromPayloadMeta(HandlePaymentWebhookInput $input): ?int
    {
        $payload = $input->payload;
        $object  = $payload['data']['object'] ?? [];

        // PaymentIntent 系
        if (isset($object['metadata']) && is_array($object['metadata'])) {
            $oid = $object['metadata']['order_id'] ?? null;
            if (is_numeric($oid)) {
                return (int) $oid;
            }
        }

        // charge.* などで metadata が別に入る場合（拡張余地）
        // 必要ならここで追加

        return null;
    }

    private function safeReserve(HandlePaymentWebhookInput $input): bool|null
    {
        try {
            return $this->webhookEvents->reserve(
                $input->provider,
                $input->eventId,
                $input->eventType,
                $input->payloadHash
            );
        } catch (\Throwable) {
            return null;
        }
    }

    private function safeComplete(
        HandlePaymentWebhookInput $input,
        string $status,
        ?int $paymentId,
        ?int $orderId,
        ?string $errorMessage,
    ): void {
        try {
            $this->webhookEvents->complete(
                $input->provider,
                $input->eventId,
                $status,
                $paymentId,
                $orderId,
                $errorMessage,
            );
        } catch (\Throwable) {
            // swallow
        }
    }
}

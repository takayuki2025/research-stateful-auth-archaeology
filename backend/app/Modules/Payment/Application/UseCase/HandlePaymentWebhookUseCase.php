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
use App\Modules\Payment\Application\UseCase\Ledger\PostLedgerFromPaymentEventUseCase;
use App\Modules\Payment\Application\Dto\Ledger\PostLedgerFromPaymentEventInput;
use App\Modules\Payment\Domain\Ledger\PostingType;
use App\Modules\Payment\Application\UseCase\Ledger\PostFeeFromStripeChargeUseCase;
use App\Modules\Payment\Domain\Ledger\Port\PostLedgerPort;
use App\Modules\Payment\Domain\Ledger\Port\PostLedgerCommand;

final class HandlePaymentWebhookUseCase
{
    public function __construct(
        private PaymentQueryRepository $webhookEvents, // reserve/complete の正（processed正統）
        private PaymentRepository $payments,
        private OrderRepository $orders,
        private ShopLedgerRepository $ledgers,
        private StripeEventMapper $mapper,
        private PostLedgerPort $port,
        private PostFeeFromStripeChargeUseCase $postFee,
    ) {
    }


    public function handle(HandlePaymentWebhookInput $input): void
    {
        // =========================
        // 0) 冪等ロック（reserve）
        // =========================
        // reserve できない = 既処理 or 競合（または異常） => 何もしない
        $reserved = $this->safeReserve($input);
        if ($reserved !== true) {
            return;
        }

        // =========================
        // 1) complete を1回だけ呼ぶための最終状態
        // =========================
        $finalStatus = 'ok';              // ok | ignored | error
        $finalPaymentId = null;           // ?int
        $finalOrderId = null;             // ?int
        $finalErrorMessage = null;        // ?string

        // afterCommit dispatch 用
        $orderPaidEvent = null;

        try {
            // =========================
            // 2) Domain Event へマップ
            // =========================
            $domainEvent = $this->mapper->map($input);

            // payload metadata から拾う（Paymentが無い救済・監査紐付けのため）
            $orderIdFromMeta = $this->extractOrderIdFromPayloadMeta($input);
            $paymentIdFromMeta = $this->extractPaymentIdFromPayloadMeta($input);

            // =========================
            // 3) IGNORED は「監査ログだけ残して終了」
            // =========================
            // - Payment/Order/Ledger は絶対に触らない
            // - complete は ignored で確定（監査的にブレない）
            if ($domainEvent->type === DomainPaymentEventType::IGNORED) {

    // ✅ 例外：charge.updated 等で balance_transaction が入ることがあるので fee だけ起こす
    if (str_starts_with($input->eventType, 'charge.')) {
        $this->handleFeeOnlyIfPossible($input, $domainEvent, $paymentIdFromMeta, $orderIdFromMeta);
    }

    $finalStatus = 'ignored';
    $finalPaymentId = is_int($paymentIdFromMeta) ? $paymentIdFromMeta : null;
    $finalOrderId = is_int($orderIdFromMeta) ? $orderIdFromMeta : null;
    return;
}

            // =========================
            // 4) 本処理（transaction）
            // =========================
            DB::transaction(function () use (
                $input,
                $domainEvent,
                $orderIdFromMeta,
                $paymentIdFromMeta,
                &$finalPaymentId,
                &$finalOrderId,
                &$orderPaidEvent
            ) {
                // -----------------------------------------
                // 4-1) Payment 探索順（R3固定）
                //  (1) provider_payment_id -> Payment
                //  (2) metadata.payment_id -> Payment
                //  (3) 無ければ order_id 救済（SUCCEEDEDのみ）
                // -----------------------------------------
                $payment = $this->payments->findByProviderPaymentId($domainEvent->providerPaymentId);

                if (! $payment && is_int($paymentIdFromMeta)) {
                    $payment = $this->payments->findById($paymentIdFromMeta);
                }

                // Payment が取れた場合は監査紐付けを確定
                if ($payment) {
                    $finalPaymentId = $payment->id();
                    $finalOrderId = $payment->orderId();
                } else {
                    // Payment が無い場合でも、メタから取れるなら監査紐付け
                    $finalPaymentId = is_int($paymentIdFromMeta) ? $paymentIdFromMeta : null;
                    $finalOrderId = is_int($orderIdFromMeta) ? $orderIdFromMeta : null;
                }

// ✅ fee は charge.* で起こす（Paymentの状態に依存させない）
if ($payment && (str_starts_with($input->eventType, 'charge.'))) {
    $charge = $input->payload['data']['object'] ?? [];

    $balanceTxnId = $charge['balance_transaction'] ?? null;

    if (is_string($balanceTxnId) && $balanceTxnId !== '') {
        $this->postFee->handle(
            balanceTransactionId: $balanceTxnId,
            shopId: $payment->shopId(),
            orderId: $payment->orderId(),
            paymentId: $payment->id(),
            occurredAt: $domainEvent->occurredAt,
            meta: [
                'provider_payment_id' => $domainEvent->providerPaymentId,
                'charge_id' => $charge['id'] ?? null,
                'webhook_event_type' => $input->eventType,
                'webhook_event_id' => $input->eventId,
            ],
        );
    }
}
                // -----------------------------------------
                // 4-2) Payment が無い救済ルート
                // -----------------------------------------
                if (! $payment) {

                    // SUCCEEDED 以外は何もしない（監査ログは complete で残る）
                    if ($domainEvent->type !== DomainPaymentEventType::SUCCEEDED) {
                        return;
                    }

                    // order_id が取れないなら何もしない
                    if (!is_int($orderIdFromMeta)) {
                        return;
                    }

                    $order = $this->orders->findById($orderIdFromMeta);
                    if (! $order) {
                        return;
                    }

                    $finalOrderId = $order->id();

                    // すでに paid なら何もしない（冪等）
                    if ($order->isPaid()) {
                        return;
                    }

                    // ✅ Order を paid に進める（時刻は Stripe occurredAt を正）
                    $paidOrder = $order->markPaid($domainEvent->occurredAt);
                    $this->orders->save($paidOrder);

                    // ✅ OrderPaid event（afterCommitでdispatch）
                    $orderPaidEvent = new OrderPaid(
                        orderId: $paidOrder->id(),
                        shopId: $paidOrder->shopId(),
                    );

                    // ✅ Ledger は “Payment不在” では原則記録しない（v2以降の整合性のため）
                    //    → 後で Payment/PI と紐づけ可能になった時に補正する方が安全
                    return;
                }

                // -----------------------------------------
                // 4-3) 安全装置：metadata.order_id と Payment.orderId の一致
                // -----------------------------------------
                if (is_int($orderIdFromMeta) && $orderIdFromMeta !== $payment->orderId()) {
                    // 別注文への誤紐付け可能性 => 触らない（監査ログだけ残す）
                    return;
                }

                // -----------------------------------------
                // 4-4) Refund
                // -----------------------------------------
                if ($domainEvent->type === DomainPaymentEventType::REFUND_SUCCEEDED) {

\Log::info('[🔥REFUND] instructions', [
  'event_type' => $input->eventType,
  'instructions' => $domainEvent->instructions ?? null,
]);

                    $meta = $domainEvent->instructions ?? [];
$refundId = $meta['provider_refund_id'] ?? null;

if (!is_string($refundId) || $refundId === '') {
    return;
}

// ✅ v2-3.2: refund 実額
$refundAmount = $meta['refund_amount'] ?? null;
if (!is_numeric($refundAmount)) {
    return;
}
$refundAmount = (int)$refundAmount;

if ($refundAmount <= 0) {
    return;
}

// ✅ currency（payload優先、なければpaymentの通貨）
$refundCurrency = $meta['currency'] ?? $payment->currency();
$refundCurrency = is_string($refundCurrency) && $refundCurrency !== ''
    ? $refundCurrency
    : $payment->currency();

// 冪等（shop_ledgers側）
if ($this->ledgers->existsRefundByProviderRefundId('stripe', $refundId)) {
    return;
}

// ✅ shop_ledgers（前段ログ）— refundAmount を渡す
$this->ledgers->recordRefund(
    shopId: $payment->shopId(),
    amount: $refundAmount,               // ★ payment->amount ではない
    currency: $refundCurrency,
    orderId: $payment->orderId(),
    paymentId: $payment->id(),
    provider: 'stripe',
    providerRefundId: $refundId,
    reason: $meta['reason'] ?? null,
    occurredAt: $domainEvent->occurredAt,
);

// ✅ v2 posting（冪等キーは refund_id:refund）
$sourceId = $refundId . ':' . PostingType::REFUND;

$this->port->post(new PostLedgerCommand(
    source_provider: 'stripe',
    source_event_id: $sourceId,
    shop_id: $payment->shopId(),
    order_id: $payment->orderId(),
    payment_id: $payment->id(),
    posting_type: PostingType::REFUND,
    amount: $refundAmount,               // ★ refund 実額（正）
    currency: $refundCurrency,
    occurred_at: $domainEvent->occurredAt->format('Y-m-d H:i:s'),
    meta: [
        'provider_payment_id' => $domainEvent->providerPaymentId,
        'provider_refund_id'  => $refundId,
        'refund_amount'       => $refundAmount,
        'webhook_event_type'  => $input->eventType,
        'webhook_event_id'    => $input->eventId,
    ],
    replay: false,
));

return;
                }

                // -----------------------------------------
                // 4-5) SUCCEEDED / FAILED / REQUIRES_ACTION
                // -----------------------------------------
                if ($input->eventType !== 'payment_intent.succeeded') {
                    return;
                }
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

                    $order = $this->orders->findById($payment->orderId());
                    if (! $order) {
                        // Paymentだけ succeeded にしておく（Orderは復旧時に別途）
                        $this->payments->save($payment->markSucceeded());
                        return;
                    }

                    $finalOrderId = $order->id();

                    // Order がすでに paid なら、Payment を succeeded に揃えて終わり（冪等）
                    if ($order->isPaid()) {
                        $this->payments->save($payment->markSucceeded());
                        return;
                    }

                    // ✅ Payment succeeded
                    $payment = $payment->markSucceeded();
                    $this->payments->save($payment);

                    // ✅ Order paid（occurredAt を正）
                    $paidOrder = $order->markPaid($domainEvent->occurredAt);
                    $this->orders->save($paidOrder);

                    // ✅ OrderPaid event
                    $orderPaidEvent = new OrderPaid(
                        orderId: $paidOrder->id(),
                        shopId: $paidOrder->shopId(),
                    );

$this->ledgers->recordSale(
    shopId: $payment->shopId(),
    amount: $payment->amount(),
    currency: $payment->currency(),
    orderId: $payment->orderId(),
    paymentId: $payment->id(),
    occurredAt: $domainEvent->occurredAt,
);

// ✅ v2 posting（冪等キーは pi_xxx:sale）
$this->port->post(new PostLedgerCommand(
  source_provider: 'stripe',
  source_event_id: $domainEvent->providerPaymentId . ':' . PostingType::SALE,
  shop_id: $payment->shopId(),
  order_id: $payment->orderId(),
  payment_id: $payment->id(),
  posting_type: PostingType::SALE,
  amount: $payment->amount(),
  currency: $payment->currency(),
  occurred_at: $domainEvent->occurredAt->format('Y-m-d H:i:s'),
  meta: [
    'provider_payment_id' => $domainEvent->providerPaymentId,
    'webhook_event_type' => $input->eventType,
    'webhook_event_id' => $input->eventId,
  ],
  replay: false,
));


                }

                // ここに来るのは基本的に無いが、念のため何もしない
            });

            // 正常終了
            $finalStatus = 'ok';

        } catch (\Throwable $e) {
            // 例外は Stripe には返さないが、監査ログには error として残す
            $finalStatus = 'error';
            $finalErrorMessage = $e->getMessage();

            // Controller が swallow する前提でも、ここで throw しておく（監視のため）
            throw $e;

        } finally {
            // =========================
            // 5) complete（必ず1回だけ）
            // =========================
            $this->safeComplete(
                $input,
                $finalStatus,
                $finalPaymentId,
                $finalOrderId,
                $finalErrorMessage
            );

            // =========================
            // 6) afterCommit dispatch
            // =========================
            if ($orderPaidEvent) {
                DB::afterCommit(fn () => Event::dispatch($orderPaidEvent));
            }
        }
    }

    /**
     * Stripe payload 内の metadata.order_id を拾う（Payment 不在救済/監査紐付け）
     */
    private function extractOrderIdFromPayloadMeta(HandlePaymentWebhookInput $input): ?int
    {
        $payload = $input->payload;
        $object  = $payload['data']['object'] ?? [];

        if (isset($object['metadata']) && is_array($object['metadata'])) {
            $oid = $object['metadata']['order_id'] ?? null;
            if (is_numeric($oid)) {
                return (int) $oid;
            }
        }

        return null;
    }

    /**
     * Stripe payload 内の metadata.payment_id を拾う（Payment 不在救済の第一候補）
     */
    private function extractPaymentIdFromPayloadMeta(HandlePaymentWebhookInput $input): ?int
    {
        $payload = $input->payload;
        $object  = $payload['data']['object'] ?? [];

        if (isset($object['metadata']) && is_array($object['metadata'])) {
            $pid = $object['metadata']['payment_id'] ?? null;
            if (is_numeric($pid)) {
                return (int) $pid;
            }
        }

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

    private function handleFeeOnlyIfPossible(
    HandlePaymentWebhookInput $input,
    $domainEvent,
    ?int $paymentIdFromMeta,
    ?int $orderIdFromMeta,
): void {
    // charge.* 以外は何もしない
    if (!str_starts_with($input->eventType, 'charge.')) {
        return;
    }

    $charge = $input->payload['data']['object'] ?? [];
    if (!is_array($charge)) {
        return;
    }

    // ① balance_transaction が無ければ fee は作れない（=何もしない）
    $balanceTxnId = $charge['balance_transaction'] ?? null;
    if (!is_string($balanceTxnId) || $balanceTxnId === '') {
        return;
    }

    // ② Payment を特定（優先順）
    $payment = null;

    // (a) metadata.payment_id が取れるなら最優先
    if (is_int($paymentIdFromMeta)) {
        $payment = $this->payments->findById($paymentIdFromMeta);
    }

    // (b) charge.payment_intent から payment を引く（pi_xxx）
    if (!$payment) {
        $piId = $charge['payment_intent'] ?? null;
        if (is_string($piId) && $piId !== '') {
            $payment = $this->payments->findByProviderPaymentId($piId);
        }
    }

    if (!$payment) {
        return; // どの注文/支払いか特定できないので触らない
    }

    // ③ fee posting（冪等キーは txn:fee で PostFeeFromStripeChargeUseCase が担保）
    $this->postFee->handle(
        balanceTransactionId: $balanceTxnId,
        shopId: $payment->shopId(),
        orderId: $orderIdFromMeta ?? $payment->orderId(),
        paymentId: $payment->id(),
        occurredAt: $domainEvent->occurredAt,
        meta: [
            'provider_payment_id' => $domainEvent->providerPaymentId,
            'charge_id' => $charge['id'] ?? null,
            'webhook_event_type' => $input->eventType,
            'webhook_event_id' => $input->eventId,
        ],
    );
}
}
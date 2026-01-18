<?php

namespace App\Modules\Payment\Application\UseCase\Ledger;

use App\Modules\Payment\Application\Dto\Ledger\PostLedgerFromPaymentEventInput;
use App\Modules\Payment\Domain\Ledger\PostingType;
use App\Modules\Payment\Domain\Port\FeeQueryPort;
use App\Modules\Payment\Domain\Ledger\Port\PostLedgerPort;
use App\Modules\Payment\Domain\Ledger\Port\PostLedgerCommand;


final class PostFeeFromStripeChargeUseCase
{
    public function __construct(
        private FeeQueryPort $fees,
        private PostLedgerPort $port,
    ) {
    }

    public function handle(
        string $balanceTransactionId,
        int $shopId,
        ?int $orderId,
        ?int $paymentId,
        \DateTimeImmutable $occurredAt,
        array $meta
    ): void {
        $fee = $this->fees->getFeeByBalanceTransactionId($balanceTransactionId);

        if ($fee->feeAmount <= 0) {
            return; // 手数料0なら何もしない
        }

        // ✅ 冪等キー：balance_transaction_id:fee
        $sourceEventId = $balanceTransactionId . ':' . PostingType::FEE;

        $cmd = new PostLedgerCommand(
    source_provider: 'stripe',
    source_event_id: $balanceTransactionId . ':' . PostingType::FEE,
    shop_id: $shopId,
    order_id: $orderId,
    payment_id: $paymentId,
    posting_type: PostingType::FEE,
    amount: $fee->feeAmount,
    currency: $fee->currency,
    occurred_at: $occurredAt->format('Y-m-d H:i:s'),
    meta: $meta + ['balance_transaction_id' => $balanceTransactionId],
    replay: false,
);

$this->port->post($cmd);
    }
}
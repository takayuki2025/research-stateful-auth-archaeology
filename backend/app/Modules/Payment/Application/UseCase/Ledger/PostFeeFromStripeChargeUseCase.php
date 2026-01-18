<?php

namespace App\Modules\Payment\Application\UseCase\Ledger;

use App\Modules\Payment\Application\Dto\Ledger\PostLedgerFromPaymentEventInput;
use App\Modules\Payment\Domain\Ledger\PostingType;
use App\Modules\Payment\Domain\Port\FeeQueryPort;

final class PostFeeFromStripeChargeUseCase
{
    public function __construct(
        private FeeQueryPort $fees,
        private PostLedgerFromPaymentEventUseCase $postLedger,
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

        $this->postLedger->handle(new PostLedgerFromPaymentEventInput(
            sourceProvider: 'stripe',
            sourceEventId: $sourceEventId,
            shopId: $shopId,
            orderId: $orderId,
            paymentId: $paymentId,
            postingType: PostingType::FEE,
            amount: $fee->feeAmount,
            currency: $fee->currency,
            occurredAt: $occurredAt,
            meta: $meta + [
                'balance_transaction_id' => $balanceTransactionId,
            ],
        ));
    }
}
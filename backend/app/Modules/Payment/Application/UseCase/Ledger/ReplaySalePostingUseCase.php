<?php

namespace App\Modules\Payment\Application\UseCase\Ledger;

use App\Modules\Payment\Application\Dto\Ledger\ReplaySalePostingInput;
use App\Modules\Payment\Application\Dto\Ledger\PostLedgerFromPaymentEventInput;
use App\Modules\Payment\Domain\Ledger\PostingType;
use App\Modules\Payment\Domain\Repository\PaymentRepository;

final class ReplaySalePostingUseCase
{
    public function __construct(
        private PaymentRepository $payments,
        private PostLedgerFromPaymentEventUseCase $postLedger,
    ) {
    }

    public function handle(ReplaySalePostingInput $in): void
    {
        $payment = $this->payments->findById($in->payment_id);
        if (! $payment) {
            throw new \DomainException('Payment not found');
        }

        if ($payment->status()->value !== 'succeeded') {
            throw new \DomainException('Payment is not succeeded');
        }

        $sourceEventId = $payment->providerPaymentId() . ':' . PostingType::SALE;

        $this->postLedger->handle(new PostLedgerFromPaymentEventInput(
            sourceProvider: 'stripe',
            sourceEventId: $sourceEventId,
            shopId: $payment->shopId(),
            orderId: $payment->orderId(),
            paymentId: $payment->id(),
            postingType: PostingType::SALE,
            amount: $payment->amount(),
            currency: $payment->currency(),
            occurredAt: new \DateTimeImmutable(), // リプレイ時刻（v2-4最小）
            meta: [
                'replay' => true,
                'provider_payment_id' => $payment->providerPaymentId(),
            ],
        ));
    }
}
<?php

namespace App\Modules\Payment\Application\UseCase\Ledger;

use App\Modules\Payment\Application\Dto\Ledger\ReplaySalePostingInput;
use App\Modules\Payment\Application\Dto\Ledger\PostLedgerFromPaymentEventInput;
use App\Modules\Payment\Domain\Ledger\PostingType;
use App\Modules\Payment\Domain\Repository\PaymentRepository;
use App\Modules\Payment\Domain\Ledger\Port\PostLedgerPort;
use App\Modules\Payment\Domain\Ledger\Port\PostLedgerCommand;


final class ReplaySalePostingUseCase
{
    public function __construct(
        private PaymentRepository $payments,
        private PostLedgerPort $port,
        // private PostLedgerFromPaymentEventUseCase $postLedger,
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

        $cmd = new PostLedgerCommand(
    source_provider: 'stripe',
    source_event_id: $payment->providerPaymentId() . ':' . PostingType::SALE,
    shop_id: $payment->shopId(),
    order_id: $payment->orderId(),
    payment_id: $payment->id(),
    posting_type: PostingType::SALE,
    amount: $payment->amount(),
    currency: $payment->currency(),
    occurred_at: (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
    meta: ['provider_payment_id' => $payment->providerPaymentId()],
    replay: true,
);

$this->port->post($cmd);
    }
}
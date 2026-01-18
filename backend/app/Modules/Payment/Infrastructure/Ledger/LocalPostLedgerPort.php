<?php

namespace App\Modules\Payment\Infrastructure\Ledger;

use App\Modules\Payment\Domain\Ledger\Port\PostLedgerPort;
use App\Modules\Payment\Domain\Ledger\Port\PostLedgerCommand;
use App\Modules\Payment\Domain\Ledger\Repository\LedgerPostingRepository;
use App\Modules\Payment\Domain\Ledger\Repository\LedgerEntryRepository;
use App\Modules\Payment\Domain\Ledger\Service\LedgerPoster;
use Illuminate\Support\Facades\DB;

final class LocalPostLedgerPort implements PostLedgerPort
{
    public function __construct(
        private LedgerPostingRepository $postings,
        private LedgerEntryRepository $entries,
        private LedgerPoster $poster,
    ) {
    }

    public function post(PostLedgerCommand $cmd): void
    {
        DB::transaction(function () use ($cmd) {

            // reserve（冪等）
            $postingId = $this->postings->reserve(
                sourceProvider: $cmd->source_provider,
                sourceEventId: $cmd->source_event_id,
                shopId: $cmd->shop_id,
                orderId: $cmd->order_id,
                paymentId: $cmd->payment_id,
                postingType: $cmd->posting_type,
                amount: $cmd->amount,
                currency: $cmd->currency,
                occurredAt: new \DateTimeImmutable($cmd->occurred_at),
                meta: $cmd->meta + ['replay' => $cmd->replay],
            );

            if ($postingId === null) {
                return; // 既処理
            }

            // entries生成
            $built = $this->poster->buildEntries($cmd->posting_type, $cmd->amount, $cmd->currency);

            if ($built['debit_total'] !== $built['credit_total']) {
                throw new \DomainException('Double-entry mismatch');
            }

            $this->entries->insertEntries($postingId, $built['entries']);
        });
    }
}
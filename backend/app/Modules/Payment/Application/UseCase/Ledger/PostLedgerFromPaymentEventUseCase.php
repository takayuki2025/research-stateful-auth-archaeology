<?php

namespace App\Modules\Payment\Application\UseCase\Ledger;

use App\Modules\Payment\Application\Dto\Ledger\PostLedgerFromPaymentEventInput;
use App\Modules\Payment\Domain\Ledger\Repository\LedgerPostingRepository;
use App\Modules\Payment\Domain\Ledger\Repository\LedgerEntryRepository;
use App\Modules\Payment\Domain\Ledger\Service\LedgerPoster;
use Illuminate\Support\Facades\DB;

final class PostLedgerFromPaymentEventUseCase
{
    public function __construct(
        private LedgerPostingRepository $postings,
        private LedgerEntryRepository $entries,
        private LedgerPoster $poster,
    ) {
    }

    public function handle(PostLedgerFromPaymentEventInput $in): void
    {
        DB::transaction(function () use ($in) {

            // ① 冪等 reserve（postingが既にあれば何もしない）
            $postingId = $this->postings->reserve(
                sourceProvider: $in->sourceProvider,
                sourceEventId: $in->sourceEventId,
                shopId: $in->shopId,
                orderId: $in->orderId,
                paymentId: $in->paymentId,
                postingType: $in->postingType,
                amount: $in->amount,
                currency: $in->currency,
                occurredAt: $in->occurredAt,
                meta: $in->meta,
            );

            if ($postingId === null) {
                return; // 既処理（冪等）
            }

            // ② double-entry 生成
            $built = $this->poster->buildEntries($in->postingType, $in->amount, $in->currency);

            // ③ 貸借一致を強制（破れたら v2失格）
            if ($built['debit_total'] !== $built['credit_total']) {
                throw new \DomainException('Double-entry mismatch');
            }

            // ④ entries insert
            $this->entries->insertEntries($postingId, $built['entries']);
        });
    }
}
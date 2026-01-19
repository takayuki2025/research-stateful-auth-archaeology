<?php

namespace App\Modules\Payment\Application\UseCase\Admin\TrustLedger;

use App\Modules\Payment\Application\Dto\Admin\TrustLedger\AdminLedgerEntryLineDto;
use App\Modules\Payment\Application\Dto\Admin\TrustLedger\AdminPostingDetailDto;
use App\Modules\Payment\Domain\Ledger\Repository\AdminLedgerPostingQueryRepository;

final class GetPostingDetailUseCase
{
    public function __construct(
        private AdminLedgerPostingQueryRepository $postings,
    ) {
    }

    public function handle(int $postingId): AdminPostingDetailDto
    {
        $r = $this->postings->getPostingDetail($postingId);

        $lines = [];
        foreach ($r['entries'] as $e) {
            $lines[] = new AdminLedgerEntryLineDto(
                account_code: (string)$e['account_code'],
                side: (string)$e['side'],
                amount: (int)$e['amount'],
                currency: (string)$e['currency'],
            );
        }

        return new AdminPostingDetailDto(
            posting: $r['posting'],
            entries: $lines,
        );
    }
}
<?php

namespace App\Modules\Payment\Application\Dto\Admin\TrustLedger;

final class AdminPostingDetailDto
{
    /** @param array<int, AdminLedgerEntryLineDto> $entries */
    public function __construct(
        public readonly array $posting,
        public readonly array $entries,
    ) {
    }

    public function toArray(): array
    {
        return [
            'posting' => $this->posting,
            'entries' => array_map(fn (AdminLedgerEntryLineDto $l) => $l->toArray(), $this->entries),
        ];
    }
}
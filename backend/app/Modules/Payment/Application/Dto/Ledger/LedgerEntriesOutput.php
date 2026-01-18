<?php

namespace App\Modules\Payment\Application\Dto\Ledger;

final class LedgerEntriesOutput
{
    /**
     * @param LedgerEntryItemOutput[] $items
     */
    public function __construct(
        public array $items,
        public ?int $next_cursor,
    ) {
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(fn($i) => $i->toArray(), $this->items),
            'next_cursor' => $this->next_cursor,
        ];
    }
}
<?php

namespace App\Modules\Payment\Domain\Ledger\Repository;

interface LedgerEntryRepository
{
    public function insertEntries(int $postingId, array $rows): void;
}
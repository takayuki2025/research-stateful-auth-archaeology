<?php

namespace App\Modules\Payment\Domain\Account\Repository;

interface BalanceRepository
{
    public function upsert(int $accountId, int $available, int $pending, string $currency, \DateTimeImmutable $calculatedAt): void;

    public function findByAccountId(int $accountId): ?array;
}
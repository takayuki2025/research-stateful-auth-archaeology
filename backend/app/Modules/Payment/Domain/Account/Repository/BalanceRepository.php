<?php

namespace App\Modules\Payment\Domain\Account\Repository;

interface BalanceRepository
{
    public function upsert(int $accountId, int $available, int $pending, int $held, string $currency, \DateTimeImmutable $calculatedAt): void;

    public function lockForUpdate(int $accountId): array;

    public function updateAmounts(int $accountId, int $available, int $pending, int $held, \DateTimeImmutable $calculatedAt): void;
}
<?php

namespace App\Modules\Payment\Domain\Account\Repository;

interface PayoutRepository
{
    public function create(
        int $accountId,
        int $amount,
        string $currency,
        string $rail,
        ?string $providerPayoutId,
        ?array $meta,
        \DateTimeImmutable $requestedAt,
    ): int;

    public function findById(int $payoutId): ?array;

    public function updateStatus(
        int $payoutId,
        string $status,
        ?string $providerPayoutId,
        ?array $meta,
        \DateTimeImmutable $now,
    ): void;
}
<?php

namespace App\Modules\Payment\Domain\Account\Repository;

interface HoldRepository
{
    public function create(int $accountId, int $amount, string $currency, string $reasonCode, ?array $meta, \DateTimeImmutable $heldAt): int;

    public function findById(int $holdId): ?array;

    public function markReleased(int $holdId, \DateTimeImmutable $releasedAt): void;
}
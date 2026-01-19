<?php

namespace App\Modules\Payment\Domain\Account\Repository;

interface AdminHoldQueryRepository
{
    /**
     * @param int[]|null $shopIds
     * @return array{items:array<int,array<string,mixed>>, next_cursor:?string}
     */
    public function listHolds(
        ?array $shopIds,
        string $from,
        string $to,
        ?string $status,
        int $limit,
        ?string $cursor
    ): array;
}
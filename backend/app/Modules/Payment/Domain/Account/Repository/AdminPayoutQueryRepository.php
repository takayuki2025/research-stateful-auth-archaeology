<?php

namespace App\Modules\Payment\Domain\Account\Repository;

interface AdminPayoutQueryRepository
{
    /**
     * @param int[]|null $shopIds
     * @return array{items:array<int,array<string,mixed>>, next_cursor:?string}
     */
    public function listPayouts(
        ?array $shopIds,
        string $from,
        string $to,
        ?string $status,
        int $limit,
        ?string $cursor
    ): array;
}

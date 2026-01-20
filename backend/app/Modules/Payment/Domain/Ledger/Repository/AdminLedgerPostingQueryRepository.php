<?php

namespace App\Modules\Payment\Domain\Ledger\Repository;

interface AdminLedgerPostingQueryRepository
{
    /**
     * @param int[]|null $shopIds
     * @return array{items:array<int,array<string,mixed>>, next_cursor:?string}
     */
    public function searchPostings(
    ?array $shopIds,
    string $from,
    string $to,
    string $currency,
    string $postingType,
    ?string $q,
    ?int $paymentId,
    ?int $orderId,
    ?string $sourceEventId,
    int $limit,
    ?string $cursor
): array;

    /** @return array{posting:array<string,mixed>, entries:array<int,array<string,mixed>>} */
    public function getPostingDetail(int $postingId): array;
}
<?php

namespace App\Modules\Payment\Domain\Ledger\Repository;

interface AdminWebhookEventQueryRepository
{
    /**
     * @return array{items:array<int,array<string,mixed>>, next_cursor:?string}
     */
    public function searchWebhookEvents(
        string $from,
        string $to,
        ?string $status,
        ?string $eventType,
        ?string $q,
        int $limit,
        ?string $cursor
    ): array;
}
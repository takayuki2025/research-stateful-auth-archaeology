<?php

namespace App\Modules\Payment\Application\UseCase\Admin\TrustLedger;

use App\Modules\Payment\Application\Dto\Admin\TrustLedger\CursorPageDto;
use App\Modules\Payment\Domain\Ledger\Repository\AdminWebhookEventQueryRepository;

final class ListWebhookEventsUseCase
{
    public function __construct(
        private AdminWebhookEventQueryRepository $events,
    ) {
    }

    public function handle(string $from, string $to, ?string $status, ?string $eventType, ?string $q, int $limit, ?string $cursor): CursorPageDto
    {
        $r = $this->events->searchWebhookEvents($from, $to, $status, $eventType, $q, $limit, $cursor);
        return new CursorPageDto($r['items'], $r['next_cursor']);
    }
}
<?php

namespace App\Modules\Payment\Application\UseCase\Admin\TrustLedger;

use App\Modules\Payment\Application\Dto\Admin\TrustLedger\CursorPageDto;
use App\Modules\Payment\Domain\Account\Repository\AdminHoldQueryRepository;

final class ListHoldsUseCase
{
    public function __construct(
        private AdminHoldQueryRepository $holds,
    ) {
    }

    public function handle(?array $shopIds, string $from, string $to, ?string $status, int $limit, ?string $cursor): CursorPageDto
    {
        $r = $this->holds->listHolds($shopIds, $from, $to, $status, $limit, $cursor);
        return new CursorPageDto($r['items'], $r['next_cursor']);
    }
}
<?php

namespace App\Modules\Order\Application\UseCase;

use App\Modules\Auth\Application\Service\AuthContext;
use App\Modules\Order\Domain\Repository\OrderHistoryQueryRepository;

final class GetMyBoughtItemsUseCase
{
    public function __construct(
        private AuthContext $auth,
        private OrderHistoryQueryRepository $histories,
    ) {
    }

    /**
     * @return array<array<string,mixed>>
     */
    public function handle(): array
    {
        $userId = $this->auth->principal()->userId;

        return $this->histories->findByBuyer($userId);
    }
}

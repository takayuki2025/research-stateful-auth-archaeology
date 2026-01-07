<?php

namespace App\Modules\Order\Domain\Repository;

interface OrderHistoryRepository
{
    public function addEvent(int $orderId, string $type, ?array $payload = null): void;

    /** @return Order[] */
    public function findByBuyer(int $userId): array;
}

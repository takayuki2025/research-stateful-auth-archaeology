<?php

namespace App\Modules\Order\Domain\Repository;

interface OrderHistoryQueryRepository
{
    /**
     * @return array<array{
     *   row_id: string,
     *   item_id: int,
     *   order_id: int,
     *   name: string,
     *   item_image: string|null,
     *   price: int|null
     * }>
     */
    public function findByBuyer(int $userId): array;
}

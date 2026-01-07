<?php

namespace App\Modules\Order\Domain\Repository;

use App\Modules\Order\Domain\Entity\Order;

interface OrderRepository
{
    public function findById(int $orderId): ?Order;

    public function findDraftByUser(int $orderId, int $userId): Order;

    public function save(Order $order): Order;

    /** @return Order[] */
    public function findByBuyer(int $userId): array;

    /** @return Order[] */
    public function findByShop(int $shopId): array;

    public function findDraftByUserAndShop(int $userId, int $shopId): ?Order;
}

<?php

namespace App\Modules\Order\Application\UseCase;

use App\Modules\Order\Domain\Repository\OrderRepository;
use DomainException;

final class ConfirmOrderUseCase
{
    public function __construct(
        private OrderRepository $orders
    ) {
    }

    /**
     * 注文が「決済に進める状態か」を検証するだけ
     * - 状態変更しない
     * - save しない（冪等）
     */
    public function handle(int $orderId, int $userId): void
    {
        $order = $this->orders->findDraftByUser($orderId, $userId);

        if (! $order) {
            throw new DomainException('Order not found or not editable');
        }

        // ★ ここが唯一の責務
        // 配送先住所が確定しているか？
        $order->assertAddressConfirmed();
    }
}

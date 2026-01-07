<?php

namespace App\Modules\Order\Domain\Repository;

interface OrderQueryRepository
{
    /**
     * 注文一覧 + 配送状態（Shipment は nullable）
     *
     * @return array{
     *   order_id:int,
     *   order_status:string,
     *   order_paid:bool,
     *   total_amount:int,
     *   currency:string,
     *   shipment_status:?string,
     *   eta:?string,
     *   destination_address:?array
     * }[]
     */
    public function findOrderListWithShipmentByShopId(int $shopId): array;
}

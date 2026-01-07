<?php

namespace App\Modules\Shipment\Application\UseCase;

use App\Modules\Shipment\Domain\Repository\ShipmentRepository;
use App\Modules\Shipment\Domain\Repository\ShipmentEventRepository;
use App\Modules\Shipment\Domain\Event\ShipmentEvent;
use App\Modules\Shipment\Domain\Event\ShipmentEventType;
use Illuminate\Support\Facades\DB;

final class InTransitShipmentUseCase
{
    public function __construct(
        private ShipmentRepository $shipments,
        private ShipmentEventRepository $events,
    ) {
    }

    public function handle(int $shipmentId): void
    {
        DB::transaction(function () use ($shipmentId) {

            // ① 先に取得（必須）
            $shipment = $this->shipments->findById($shipmentId);

            if (!$shipment) {
                // 必要なら例外
                return;
            }

            // ② 状態チェック（ここで初めて使える）
            if ($shipment->status()->isInTransit()) {
                return; // 冪等
            }

            // ③ 状態遷移（Entity が新インスタンスを返す設計）
            $inTransitShipment = $shipment->InTransit();

            // ④ 永続化
            $this->shipments->save($inTransitShipment);

            // ⑤ イベント記録
            $this->events->record(
                ShipmentEvent::inTransit($shipmentId)
            );
        });
    }
}

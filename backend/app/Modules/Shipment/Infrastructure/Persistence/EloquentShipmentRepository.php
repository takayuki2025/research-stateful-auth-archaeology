<?php

namespace App\Modules\Shipment\Infrastructure\Persistence;

use App\Modules\Shipment\Domain\Entity\Shipment;
use App\Modules\Shipment\Domain\Enum\ShipmentStatus;
use App\Modules\Shipment\Domain\Repository\ShipmentRepository;
use App\Modules\Order\Domain\ValueObject\Address;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class EloquentShipmentRepository implements ShipmentRepository
{
    public function findById(int $shipmentId): Shipment
    {
        $row = DB::table('shipments')->where('id', $shipmentId)->first();

        if (! $row) {
            throw new RuntimeException('Shipment not found');
        }

        return $this->reconstitute($row);
    }

    public function findByOrderId(int $orderId): ?Shipment
    {
        $row = DB::table('shipments')->where('order_id', $orderId)->first();
        return $row ? $this->reconstitute($row) : null;
    }

    public function existsByOrderId(int $orderId): bool
    {
        return DB::table('shipments')->where('order_id', $orderId)->exists();
    }

    public function save(Shipment $shipment): Shipment
    {
        if ($shipment->id() === null) {

            $id = DB::table('shipments')->insertGetId([
                'shop_id' => $shipment->shopId(),
                'order_id' => $shipment->orderId(),
                'status' => $shipment->status()->value,
                'origin_address' => json_encode(
                    $shipment->originAddress()->toArray(),
                    JSON_UNESCAPED_UNICODE
                ),
                'destination_address' => json_encode(
                    $shipment->destinationAddress()->toArray(),
                    JSON_UNESCAPED_UNICODE
                ),
                'eta' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $this->findById($id);
        }

        // ★ ここで id=null を許さない
        if ($shipment->id() === null) {
            throw new RuntimeException('Cannot update Shipment without ID');
        }

        DB::table('shipments')
            ->where('id', $shipment->id())
            ->update([
                'status' => $shipment->status()->value,
                'eta' => $shipment->eta()?->format('Y-m-d H:i:s'),
                'updated_at' => now(),
            ]);

        return $this->findById($shipment->id());
    }

    private function reconstitute(object $row): Shipment
    {
        return Shipment::reconstitute(
            id: (int) $row->id,
            shopId: (int) $row->shop_id,
            orderId: (int) $row->order_id,
            status: ShipmentStatus::from($row->status),
            originAddress: Address::fromArray(json_decode($row->origin_address, true)),
            destinationAddress: Address::fromArray(json_decode($row->destination_address, true)),
            eta: $row->eta ? new \DateTimeImmutable($row->eta) : null,
        );
    }

}

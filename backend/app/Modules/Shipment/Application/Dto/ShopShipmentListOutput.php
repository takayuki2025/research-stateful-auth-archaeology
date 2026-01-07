<?php

namespace App\Modules\Shipment\Application\Dto;

final class ShopShipmentListOutput
{
    /**
     * @param ShopShipmentListItemOutput[] $items
     */
    public function __construct(
        public readonly array $items
    ) {
    }

    public function toArray(): array
    {
        return [
            'shipments' => array_map(
                fn (ShopShipmentListItemOutput $i) => $i->toArray(),
                $this->items
            ),
        ];
    }
}

<?php

namespace App\Modules\Shipment\Domain\Entity;

use App\Modules\Shipment\Domain\Enum\ShipmentStatus;
use App\Modules\Order\Domain\ValueObject\Address;
use DomainException;

/**
 * Shipment Aggregate Root
 *
 * Occ_Shipment_v1 固定仕様：
 * - OrderPaid を起点に DRAFT 作成
 * - 状態遷移は Entity のみが知る
 * - Address はスナップショット（不変）
 * - 冪等性は UseCase / Repository 側で担保
 */
final class Shipment
{
    private function __construct(
        private ?int $id,
        private int $shopId,
        private int $orderId,
        private ShipmentStatus $status,
        private Address $originAddress,
        private Address $destinationAddress,
        private ?\DateTimeImmutable $eta,
    ) {
    }

    /* =====================================================
     * Factory
     * ===================================================== */

    public static function createDraft(
        int $shopId,
        int $orderId,
        Address $originAddress,
        Address $destinationAddress,
    ): self {
        return new self(
            id: null,
            shopId: $shopId,
            orderId: $orderId,
            status: ShipmentStatus::DRAFT,
            originAddress: $originAddress,
            destinationAddress: $destinationAddress,
            eta: null,
        );
    }

    public static function reconstitute(
        int $id,
        int $shopId,
        int $orderId,
        ShipmentStatus $status,
        Address $originAddress,
        Address $destinationAddress,
        ?\DateTimeImmutable $eta,
    ): self {
        return new self(
            id: $id,
            shopId: $shopId,
            orderId: $orderId,
            status: $status,
            originAddress: $originAddress,
            destinationAddress: $destinationAddress,
            eta: $eta,
        );
    }

    /* =====================================================
     * Getters
     * ===================================================== */

    public function id(): ?int
    {
        return $this->id;
    }
    public function shopId(): int
    {
        return $this->shopId;
    }
    public function orderId(): int
    {
        return $this->orderId;
    }
    public function status(): ShipmentStatus
    {
        return $this->status;
    }
    public function originAddress(): Address
    {
        return $this->originAddress;
    }
    public function destinationAddress(): Address
    {
        return $this->destinationAddress;
    }
    public function eta(): ?\DateTimeImmutable
    {
        return $this->eta;
    }

    /* =====================================================
     * State Transitions（Occ_Shipment_v1）
     * ===================================================== */

    public function pack(): self
    {
        if ($this->status !== ShipmentStatus::DRAFT) {
            throw new DomainException('Shipment can only be packed from DRAFT');
        }

        return $this->withStatus(ShipmentStatus::PACKED);
    }

    public function ship(\DateTimeImmutable $eta): self
    {
        if ($this->status !== ShipmentStatus::PACKED) {
            throw new DomainException('Shipment can only be shipped from PACKED');
        }

        return new self(
            id: $this->id,
            shopId: $this->shopId,
            orderId: $this->orderId,
            status: ShipmentStatus::SHIPPED,
            originAddress: $this->originAddress,
            destinationAddress: $this->destinationAddress,
            eta: $eta,
        );
    }

    public function InTransit(): self
    {
        if ($this->status !== ShipmentStatus::SHIPPED) {
            throw new DomainException(
                'Shipment can only be marked in-transit from SHIPPED'
            );
        }

        return $this->withStatus(ShipmentStatus::IN_TRANSIT);
    }

    public function deliver(): self
    {
        if (! in_array($this->status, [
            ShipmentStatus::SHIPPED,
            ShipmentStatus::IN_TRANSIT,
        ], true)) {
            throw new DomainException(
                'Shipment can only be delivered after shipping'
            );
        }

        return $this->withStatus(ShipmentStatus::DELIVERED);
    }

    /* =====================================================
     * Internal
     * ===================================================== */

    private function withStatus(ShipmentStatus $status): self
    {
        return new self(
            id: $this->id,
            shopId: $this->shopId,
            orderId: $this->orderId,
            status: $status,
            originAddress: $this->originAddress,
            destinationAddress: $this->destinationAddress,
            eta: $this->eta,
        );
    }
}

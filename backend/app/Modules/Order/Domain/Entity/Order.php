<?php

namespace App\Modules\Order\Domain\Entity;

use App\Modules\Order\Domain\Enum\OrderStatus;
use App\Modules\Order\Application\Dto\OrderItemSnapshot;
use App\Modules\Order\Domain\ValueObject\Address;
use DomainException;

final class Order
{
    /**
     * @param OrderItemSnapshot[] $items
     */
    private function __construct(
        private ?int $id,
        private int $shopId,
        private int $userId,
        private OrderStatus $status,
        private int $totalAmount,
        private string $currency,
        private array $items,
        private ?array $meta,
        private ?Address $shippingAddress = null,
        private ?\DateTimeImmutable $addressSnapshotAt = null,
        private ?\DateTimeImmutable $paidAt = null,
    ) {
        if ($this->totalAmount < 0) {
            throw new DomainException('totalAmount must be >= 0');
        }
        if ($this->currency === '') {
            throw new DomainException('currency is required');
        }
        if (count($this->items) === 0) {
            throw new DomainException('items must not be empty');
        }
    }

    /* =========================================================
     | Factory
     ========================================================= */

    public static function create(
        int $shopId,
        int $userId,
        int $totalAmount,
        string $currency,
        array $items,
        ?array $meta = null
    ): self {
        return new self(
            id: null,
            shopId: $shopId,
            userId: $userId,
            status: OrderStatus::PENDING_PAYMENT,
            totalAmount: $totalAmount,
            currency: $currency,
            items: $items,
            meta: $meta
        );
    }

    public static function reconstitute(
        int $id,
        int $shopId,
        int $userId,
        OrderStatus $status,
        int $totalAmount,
        string $currency,
        array $items,
        ?array $meta = null,
        ?Address $shippingAddress = null,
        ?\DateTimeImmutable $addressSnapshotAt = null,
        ?\DateTimeImmutable $paidAt = null,
    ): self {
        return new self(
            $id,
            $shopId,
            $userId,
            $status,
            $totalAmount,
            $currency,
            $items,
            $meta,
            $shippingAddress,
            $addressSnapshotAt,
            $paidAt
        );
    }

    /* =========================================================
     | Getters
     ========================================================= */

    public function id(): ?int
    {
        return $this->id;
    }

    public function shopId(): int
    {
        return $this->shopId;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    public function totalAmount(): int
    {
        return $this->totalAmount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    /** @return OrderItemSnapshot[] */
    public function items(): array
    {
        return $this->items;
    }

    public function meta(): ?array
    {
        return $this->meta;
    }

    /* =========================================================
     | Address（購入者配送先・スナップショット）
     ========================================================= */

    /**
     * Repository 用（nullable）
     */
    public function shippingAddress(): ?Address
    {
        return $this->shippingAddress;
    }

    /**
     * 配送先が確定しているかの検証
     */
    public function assertAddressConfirmed(): void
    {
        if ($this->shippingAddress === null) {
            throw new DomainException('Address not confirmed');
        }
    }

    /**
     * 購入者の配送先をスナップショットとして確定
     */
    public function confirmAddress(
        Address $address,
        \DateTimeImmutable $now
    ): void {
        if ($this->status !== OrderStatus::PENDING_PAYMENT) {
            throw new DomainException(
                'Address can only be fixed before payment'
            );
        }

        $this->shippingAddress = $address;
        $this->addressSnapshotAt = $now;
    }

    public function addressSnapshotAt(): ?\DateTimeImmutable
    {
        return $this->addressSnapshotAt;
    }

    /* =========================================================
     | Payment
     ========================================================= */

    /**
     * 決済完了（Stripe / コンビニ Webhook 共通）
     * - ここでのみ Order の状態が変わる
     */
    public function markPaid(): self
    {
        $this->assertAddressConfirmed();

        if ($this->status === OrderStatus::PAID) {
            return $this; // 冪等
        }

        if ($this->status !== OrderStatus::PENDING_PAYMENT) {
            throw new DomainException(
                'Order cannot be marked paid from status: ' . $this->status->value
            );
        }

        return self::reconstitute(
            id: $this->id ?? 0,
            shopId: $this->shopId,
            userId: $this->userId,
            status: OrderStatus::PAID,
            totalAmount: $this->totalAmount,
            currency: $this->currency,
            items: $this->items,
            meta: $this->meta,
            shippingAddress: $this->shippingAddress,
            addressSnapshotAt: $this->addressSnapshotAt,
            paidAt: new \DateTimeImmutable(),
        );
    }

    public function paidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function isPaid(): bool
    {
        return $this->status === OrderStatus::PAID;
    }
}

<?php

namespace App\Modules\Order\Application\Dto;

final class OrderItemSnapshot
{
    public function __construct(
        public readonly int $itemId,
        public readonly string $name,
        public readonly int $priceAmount,
        public readonly string $priceCurrency,
        public readonly ?string $condition,
        /** @var string[] */
        public readonly array $category,
        public readonly ?string $imagePath,
        public readonly int $quantity,
    ) {
        if ($this->priceAmount < 0) {
            throw new \InvalidArgumentException('priceAmount must be >= 0');
        }
        if ($this->quantity <= 0) {
            throw new \InvalidArgumentException('quantity must be >= 1');
        }
    }

    /* =========================
       Getter（← ★これだけ追加）
       ========================= */

    public function itemId(): int
    {
        return $this->itemId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function imagePath(): ?string
    {
        return $this->imagePath;
    }

    public function priceAmount(): int
    {
        return $this->priceAmount;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    /* ========================= */

    public function toArray(): array
    {
        return [
            'item_id'        => $this->itemId,
            'name'           => $this->name,
            'price_amount'   => $this->priceAmount,
            'price_currency' => $this->priceCurrency,
            'condition'      => $this->condition,
            'category'       => $this->category,
            'image_path'     => $this->imagePath,
            'quantity'       => $this->quantity,
        ];
    }

    public static function fromArray(array $row): self
    {
        return new self(
            itemId: (int) $row['item_id'],
            name: (string) $row['name'],
            priceAmount: (int) $row['price_amount'],
            priceCurrency: (string) $row['price_currency'],
            condition: isset($row['condition']) ? (string) $row['condition'] : null,
            category: is_array($row['category'] ?? null) ? $row['category'] : [],
            imagePath: isset($row['image_path']) ? (string) $row['image_path'] : null,
            quantity: (int) ($row['quantity'] ?? 1),
        );
    }
}

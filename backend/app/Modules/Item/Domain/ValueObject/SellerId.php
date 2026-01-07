<?php

namespace App\Modules\Item\Domain\ValueObject;

final class SellerId
{
    private function __construct(
        private SellerType $type,
        private ?int $id,
        private string $raw, // SoT
    ) {
    }

    /* ========= Factory ========= */

    public static function user(int $userId): self
    {
        return new self(
            SellerType::INDIVIDUAL,
            $userId,
            'individual:' . $userId
        );
    }

    public static function shop(?int $shopId): self
    {
        return $shopId === null
            ? new self(SellerType::SHOP, null, 'shop:managed')
            : new self(SellerType::SHOP, $shopId, 'shop:' . $shopId);
    }

    public static function fromRaw(string $raw): self
    {
        $raw = trim($raw);

        if (str_starts_with($raw, 'individual:')) {
            return new self(
                SellerType::INDIVIDUAL,
                (int) substr($raw, 11),
                $raw
            );
        }

        if (str_starts_with($raw, 'shop:')) {
            $tail = substr($raw, 5);
            return $tail === 'managed'
                ? new self(SellerType::SHOP, null, $raw)
                : new self(SellerType::SHOP, (int) $tail, $raw);
        }

        throw new \DomainException('Invalid seller_id');
    }

    /* ========= Getter ========= */

    public function type(): SellerType
    {
        return $this->type;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function raw(): string
    {
        return $this->raw;
    }

    /**
     * ID 必須な文脈（Publish 等）
     */
    public function requireId(): int
    {
        if ($this->id === null) {
            throw new \DomainException('SellerId is not resolved');
        }
        return $this->id;
    }
}

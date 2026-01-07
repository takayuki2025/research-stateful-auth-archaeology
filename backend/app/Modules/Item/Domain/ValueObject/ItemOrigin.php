<?php

namespace App\Modules\Item\Domain\ValueObject;

final class ItemOrigin
{
    public const SHOP_MANAGED = 'shop_managed';
    public const USER_PERSONAL = 'user_personal';

    private function __construct(
        private string $value
    ) {
    }

    public static function from(string $value): self
    {
        if (!in_array($value, [self::SHOP_MANAGED, self::USER_PERSONAL], true)) {
            throw new \InvalidArgumentException("Invalid ItemOrigin: {$value}");
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    /* =========================
       ✅ 追加するメソッド
    ========================= */

    public function isUserPersonal(): bool
    {
        return $this->value === self::USER_PERSONAL;
    }

    public function isShopManaged(): bool
    {
        return $this->value === self::SHOP_MANAGED;
    }
}

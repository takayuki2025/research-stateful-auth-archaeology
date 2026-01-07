<?php

namespace App\Modules\Reaction\Domain\ValueObject;

/**
 * ReactorId
 *
 * 「リアクションを行う主体（User）」を表す ValueObject
 * - Favorite / Like / Reaction の user_id に相当
 * - DDD 的に int を直接使わないためのラッパー
 */
final class ReactorId
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException(
                'ReactorId must be a positive integer.'
            );
        }

        $this->value = $value;
    }

    /**
     * int 値を取得（Infrastructure 層向け）
     */
    public function value(): int
    {
        return $this->value;
    }

    /**
     * 同値比較（Domain 内で使用）
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

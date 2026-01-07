<?php

namespace App\Modules\Item\Domain\ValueObject;

use Ramsey\Uuid\Uuid;

final class ItemDraftId
{
    private function __construct(
        private string $value
    ) {
    }

    /* ========= Factory ========= */

    /**
     * 新規作成用
     */
    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    /**
     * Repository 再構築専用（★これが必要）
     */
    public static function fromString(string $value): self
    {
        if (! Uuid::isValid($value)) {
            throw new \DomainException('Invalid ItemDraftId');
        }

        return new self($value);
    }

    /* ========= Getter ========= */

    public function value(): string
    {
        return $this->value;
    }
}

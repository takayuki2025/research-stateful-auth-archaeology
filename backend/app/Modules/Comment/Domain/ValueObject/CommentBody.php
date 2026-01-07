<?php

namespace App\Modules\Comment\Domain\ValueObject;

use DomainException;

final class CommentBody
{
    public function __construct(private readonly string $value)
    {
        if (trim($value) === '') {
            throw new DomainException('コメントは空にできません');
        }

        if (mb_strlen($value) > 1000) {
            throw new DomainException('コメントは1000文字以内です');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}

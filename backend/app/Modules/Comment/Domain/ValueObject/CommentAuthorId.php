<?php

namespace App\Modules\Comment\Domain\ValueObject;

final class CommentAuthorId
{
    public function __construct(private readonly int $value)
    {
    }
    public function value(): int
    {
        return $this->value;
    }
}

<?php

namespace App\Modules\Auth\Domain\Entity;

class AuthUser
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
    ) {
    }
}

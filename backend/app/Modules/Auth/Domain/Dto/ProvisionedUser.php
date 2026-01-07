<?php

namespace App\Modules\Auth\Domain\Dto;


final class ProvisionedUser
{
    public function __construct(
        public readonly int $userId,
        public readonly ?string $email,
        public readonly bool $emailVerified,
        public readonly bool $isFirstLogin,
        public readonly array $shopIds,
        public readonly array $roles = [],
        public readonly ?int $tenantId = null,
    ) {
    }
}

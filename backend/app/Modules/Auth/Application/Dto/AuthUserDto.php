<?php

namespace App\Modules\Auth\Application\Dto;

use App\Modules\Auth\Domain\ValueObject\AuthPrincipal;
use App\Modules\User\Domain\Entity\Profile;

final class AuthUserDto
{
    public function __construct(
        public int $id,
        public string $email,
        public bool $emailVerified,
        public ?string $displayName,
        public bool $hasProfile,
        public bool $hasShop,
        public array $shopRoles,
    ) {
    }

    public static function fromPrincipalWithProfile(
        AuthPrincipal $principal,
        ?Profile $profile,          // ★ nullable に変更
        array $shopRoles,
        bool $hasProfile,
    ): self {
        return new self(
            id: $principal->userId,
            email: $principal->email ?? '',
            emailVerified: $principal->emailVerified,
            displayName: $profile?->displayName(), // ★ null safe
            hasProfile: $hasProfile,
            hasShop: !empty($shopRoles),
            shopRoles: $shopRoles,
        );
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'email'          => $this->email,
            'email_verified' => $this->emailVerified,
            'display_name'   => $this->displayName,
            'has_profile'    => $this->hasProfile,
            'has_shop'       => $this->hasShop,
            'shop_roles'     => $this->shopRoles,
        ];
    }
}

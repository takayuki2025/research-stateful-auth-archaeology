<?php

namespace App\Modules\Auth\Domain\ValueObject;

use App\Models\User;

/**
 * AuthPrincipal
 *
 * - 認証方式に依存しない「唯一の認証主体」
 * - Sanctum / JWT / Auth0 / 自前JWT 共通
 * - AuthContext にのみ保存される
 */
final class AuthPrincipal
{
    private function __construct(
        private int $userId,
        private string $email,
        private string $provider,      // sanctum | jwt | auth0 | internal
        private string $providerUid,   // provider 側の subject
        private bool $emailVerified,
        private array $roles = [],     // global roles
        private array $shopRoles = [], // shop_id => roles
    ) {}

    /* =====================================================
     * Factory methods
     * ===================================================== */

    /**
     * Sanctum (Laravel session / cookie)
     */
    public static function fromSanctumUser(User $user): self
    {
        return new self(
            userId: $user->id,
            email: $user->email,
            provider: 'sanctum',
            providerUid: (string) $user->id,
            emailVerified: (bool) $user->email_verified_at,
            roles: [],      // 後続で RoleResolver に任せる
            shopRoles: [],
        );
    }

    /**
     * JWT / External IdP
     */
    public static function fromJwtPayload(
        int $userId,
        string $email,
        string $provider,
        string $providerUid,
        bool $emailVerified,
        array $roles = [],
        array $shopRoles = [],
    ): self {
        return new self(
            userId: $userId,
            email: $email,
            provider: $provider,
            providerUid: $providerUid,
            emailVerified: $emailVerified,
            roles: $roles,
            shopRoles: $shopRoles,
        );
    }

    /* =====================================================
     * Accessors（UseCase / Domain から安全に参照）
     * ===================================================== */

    public function userId(): int
    {
        return $this->userId;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function providerUid(): string
    {
        return $this->providerUid;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function roles(): array
    {
        return $this->roles;
    }

    public function shopRoles(): array
    {
        return $this->shopRoles;
    }

    /* =====================================================
     * Guards
     * ===================================================== */

    public function requireEmailVerified(): void
    {
        if (! $this->emailVerified) {
            throw new \DomainException('Email not verified');
        }
    }

    public function requireRole(string $role): void
    {
        if (! in_array($role, $this->roles, true)) {
            throw new \DomainException("Role '{$role}' is required");
        }
    }

    public function requireShopRole(int $shopId, string $role): void
    {
        $roles = $this->shopRoles[$shopId] ?? [];

        if (! in_array($role, $roles, true)) {
            throw new \DomainException("Shop role '{$role}' is required");
        }
    }

    /* =====================================================
     * Debug / Log
     * ===================================================== */

    public function toArray(): array
    {
        return [
            'user_id'        => $this->userId,
            'email'          => $this->email,
            'provider'       => $this->provider,
            'provider_uid'   => $this->providerUid,
            'email_verified' => $this->emailVerified,
            'roles'          => $this->roles,
            'shop_roles'     => $this->shopRoles,
        ];
    }
}
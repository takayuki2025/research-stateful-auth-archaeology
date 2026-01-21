<?php

namespace App\Modules\Auth\Domain\Service;

use App\Modules\Auth\Domain\Dto\ProvisionedUser;
use App\Modules\Auth\Domain\ValueObject\AuthPrincipal;
use Firebase\JWT\JWT;

final class TokenIssuerService
{
    private string $secret;
    private string $issuer;
    private int $ttl;

    public function __construct()
    {
        $this->secret = config('jwt.secret');
        $this->issuer = config('jwt.issuer', 'omnicommerce-core');
        $this->ttl    = (int) config('jwt.ttl', 3600);
    }

    /**
     * ✅ Access JWT 発行専用
     */
    public function issue(ProvisionedUser $user, AuthPrincipal $principal): string
    {
        $now = time();

        $payload = [
            'iss'      => $this->issuer,
            'iat'      => $now,
            'exp'      => $now + $this->ttl,

            'sub'      => $user->userId,
            'roles'    => $user->roles ?? [],
            'shop_ids' => $user->shopIds ?? [],
            'shop_id'  => $user->tenantId,
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }
}
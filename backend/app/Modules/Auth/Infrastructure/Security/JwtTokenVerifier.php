<?php

namespace App\Modules\Auth\Infrastructure\Security;

use App\Modules\Auth\Domain\Port\TokenVerifierPort;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class JwtTokenVerifier implements TokenVerifierPort
{
    private string $secret;

    public function __construct()
    {
        $this->secret = config('jwt.secret');
    }

    public function decode(string $jwt): object
    {
        // HS256（現状）: 将来は RS256/JWKS/kid へ差し替え可能
        return JWT::decode($jwt, new Key($this->secret, 'HS256'));
    }
}

<?php

namespace App\Modules\Auth\Infrastructure\Security;

use App\Modules\Auth\Domain\Dto\DecodedToken;
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

    public function decode(string $jwt): DecodedToken
    {
        // HS256（現状）: 将来は RS256/JWKS/kid へ差し替え可能
        $payload = JWT::decode($jwt, new Key($this->secret, 'HS256'));

        // provider は暫定で 'custom' か 'token' でOK
        // （後で firebase/auth0/cognito を識別したい場合は iss/jwks で分岐）
        return new DecodedToken(
            provider: 'custom',
            payload: $payload
        );
    }
}
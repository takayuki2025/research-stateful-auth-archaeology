<?php

namespace App\Modules\Auth\Infrastructure\Security;

use App\Modules\Auth\Domain\Dto\DecodedToken;
use App\Modules\Auth\Domain\Port\TokenVerifierPort;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class CognitoJwksTokenVerifier implements TokenVerifierPort
{
    public function __construct(
        private readonly string $issuer,
        private readonly string $audience,
        private readonly int $jwksCacheSeconds = 3600,
    ) {}

    public function decode(string $jwt): DecodedToken
    {
        $header = $this->decodePart($jwt, 0);
        $payloadPart = $this->decodePart($jwt, 1);

        $alg = (string)($header['alg'] ?? '');
        $kid = (string)($header['kid'] ?? '');

        if ($alg !== 'RS256') {
            throw new \UnexpectedValueException("cognito: unsupported alg={$alg}");
        }
        if ($kid === '') {
            throw new \UnexpectedValueException("cognito: missing kid");
        }

        // 1) 署名検証（JWKS）
        $keys = $this->getKeySet(); // array of Key objects keyed by kid
        $payloadObj = JWT::decode($jwt, $keys);

        // 2) claim検証（issuer / aud / token_use）
        $iss = (string)($payloadObj->iss ?? '');
        if ($iss !== $this->issuer) {
            throw new \UnexpectedValueException("cognito: issuer mismatch (got={$iss})");
        }

        $tokenUse = (string)($payloadObj->token_use ?? '');
        if ($tokenUse !== 'id') {
            // AccessToken は client_id / scope など構造が違うので reject
            throw new \UnexpectedValueException("cognito: token_use must be id (got={$tokenUse})");
        }

        $aud = (string)($payloadObj->aud ?? '');
        if ($aud !== $this->audience) {
            throw new \UnexpectedValueException("cognito: aud mismatch (got={$aud})");
        }

        if (!isset($payloadObj->sub)) {
            throw new \UnexpectedValueException("cognito: missing sub");
        }

        return new DecodedToken('cognito', $payloadObj);
    }

    private function getKeySet(): array
    {
        $cacheKey = 'auth:cognito:jwks:' . sha1($this->issuer);

        $jwks = Cache::remember($cacheKey, $this->jwksCacheSeconds, function () {
            $url = rtrim($this->issuer, '/') . '/.well-known/jwks.json';
            $res = Http::timeout(5)->acceptJson()->get($url);
            if (!$res->ok()) {
                throw new \RuntimeException("cognito: jwks fetch failed ({$res->status()})");
            }
            return $res->json();
        });

        if (!is_array($jwks) || !isset($jwks['keys'])) {
            throw new \RuntimeException("cognito: invalid jwks");
        }

        // firebase/php-jwt が期待する Key[] に変換
        return JWK::parseKeySet($jwks);
    }

    private function decodePart(string $jwt, int $index): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) < 2) throw new \UnexpectedValueException("cognito: invalid jwt format");

        $b64 = $parts[$index] ?? '';
        $json = JWT::jsonDecode(JWT::urlsafeB64Decode($b64));
        return (array)$json;
    }
}
<?php

namespace App\Modules\Auth\Infrastructure\Security;

use App\Modules\Auth\Domain\Dto\DecodedToken;
use App\Modules\Auth\Domain\Port\TokenVerifierPort;
use Firebase\JWT\CachedKeySet;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use UnexpectedValueException;

final class MultiProviderJwtVerifier implements TokenVerifierPort
{
    public function decode(string $jwt): DecodedToken
    {
        $providers = config('jwt_providers.enabled', []);
        $errors = [];

        foreach ($providers as $name) {
            $cfg = config("jwt_providers.$name");
            if (!$cfg) continue;

            try {
                if (($cfg['type'] ?? null) === 'hs256') {
                    return $this->decodeHs256($jwt, $cfg);
                }

                if (($cfg['type'] ?? null) === 'jwks') {
                    return $this->decodeJwks($jwt, $cfg);
                }
            } catch (\Throwable $e) {
                $errors[$name] = $e->getMessage();
                continue;
            }
        }

        // 全滅 → invalid token
        throw new UnexpectedValueException('JWT verification failed: ' . json_encode($errors, JSON_UNESCAPED_UNICODE));
    }

    private function decodeHs256(string $jwt, array $cfg): DecodedToken
    {
        JWT::$leeway = (int)($cfg['leeway'] ?? 0);

        $secret = (string)($cfg['secret'] ?? '');
        if ($secret === '') {
            throw new \RuntimeException('JWT_SECRET is not configured');
        }

        $payload = JWT::decode($jwt, new Key($secret, 'HS256'));

        // iss チェック（任意だが推奨）
        if (isset($cfg['issuer']) && isset($payload->iss) && $payload->iss !== $cfg['issuer']) {
            throw new UnexpectedValueException('issuer mismatch');
        }

        return new DecodedToken(provider: (string)$cfg['provider'], payload: $payload);
    }

    private function decodeJwks(string $jwt, array $cfg): DecodedToken
    {
        JWT::$leeway = (int)($cfg['leeway'] ?? 0);

        [$jwksUri, $expectedIssuer, $expectedAudience] = $this->materializeProviderConfig($cfg);

        $keySet = $this->buildCachedKeySet($jwksUri, $cfg);

        // 署名検証（kidローテに応じてCachedKeySetがリフレッシュする）:contentReference[oaicite:7]{index=7}
        $payload = JWT::decode($jwt, $keySet);

        // 署名検証後に claim を検査（未検証issは信じない）
        if ($expectedIssuer !== null) {
            if (!isset($payload->iss) || $payload->iss !== $expectedIssuer) {
                throw new UnexpectedValueException('issuer mismatch');
            }
        }

        if ($expectedAudience !== null) {
            // provider別に audience の場所が違う場合がある
            // - Firebase: aud = project_id :contentReference[oaicite:8]{index=8}
            // - Auth0: aud = API audience :contentReference[oaicite:9]{index=9}
            // - Cognito(access token): aud ではなく client_id の場合もある（token_useで分岐推奨）:contentReference[oaicite:10]{index=10}
            $aud = $payload->aud ?? null;
            $clientId = $payload->client_id ?? null;

            $ok = false;

            if (is_string($aud) && $aud === $expectedAudience) $ok = true;
            if (is_array($aud) && in_array($expectedAudience, $aud, true)) $ok = true;

            // Cognito access token 対応（必要なら）
            if (!$ok && is_string($clientId) && $clientId === $expectedAudience) $ok = true;

            if (!$ok) {
                throw new UnexpectedValueException('audience mismatch');
            }
        }

        // Cognito: token_useをチェック（推奨）:contentReference[oaicite:11]{index=11}
        if (($cfg['provider'] ?? null) === 'cognito' && isset($cfg['expected_token_use'])) {
            $tokenUse = $payload->token_use ?? null;
            if ($tokenUse !== $cfg['expected_token_use']) {
                throw new UnexpectedValueException('token_use mismatch');
            }
        }

        // Firebase: iss/aud必須 + subは非空uid :contentReference[oaicite:12]{index=12}
        if (($cfg['provider'] ?? null) === 'firebase') {
            if (!isset($payload->sub) || (string)$payload->sub === '') {
                throw new UnexpectedValueException('firebase sub missing');
            }
        }

        return new DecodedToken(provider: (string)$cfg['provider'], payload: $payload);
    }

    private function materializeProviderConfig(array $cfg): array
    {
        $provider = $cfg['provider'] ?? null;

        if ($provider === 'firebase') {
            $projectId = (string)($cfg['project_id'] ?? '');
            if ($projectId === '') {
                throw new \RuntimeException('FIREBASE_PROJECT_ID is not configured');
            }

            $issuer = "https://securetoken.google.com/{$projectId}";
            $audience = $projectId;
            $jwksUri = (string)$cfg['jwks_uri'];

            return [$jwksUri, $issuer, $audience];
        }

        if ($provider === 'cognito') {
            $region = (string)($cfg['region'] ?? '');
            $poolId = (string)($cfg['user_pool_id'] ?? '');
            $clientId = (string)($cfg['client_id'] ?? '');

            if ($region === '' || $poolId === '' || $clientId === '') {
                throw new \RuntimeException('COGNITO_REGION / COGNITO_USER_POOL_ID / COGNITO_APP_CLIENT_ID is not configured');
            }

            $issuer = "https://cognito-idp.{$region}.amazonaws.com/{$poolId}";
            $jwksUri = "{$issuer}/.well-known/jwks.json";

            // audienceは ID token なら aud、access token なら client_id を見ることがあるため同じ値を渡しておく
            return [$jwksUri, $issuer, $clientId];
        }

        if ($provider === 'auth0') {
            $domain = (string)($cfg['domain'] ?? '');
            $audience = (string)($cfg['audience'] ?? '');
            if ($domain === '' || $audience === '') {
                throw new \RuntimeException('AUTH0_DOMAIN / AUTH0_AUDIENCE is not configured');
            }

            $issuer = "https://{$domain}/";
            $jwksUri = "{$issuer}.well-known/jwks.json";

            return [$jwksUri, $issuer, $audience];
        }

        throw new \RuntimeException('unknown jwks provider config');
    }

    private function buildCachedKeySet(string $jwksUri, array $cfg): CachedKeySet
    {
        $httpClient = new GuzzleClient();
        $httpFactory = new HttpFactory();

        // PSR-6 cache pool（ファイルで十分。Redisにしてもよい）
        $cache = new FilesystemAdapter(
            namespace: 'jwks',
            defaultLifetime: 3600,
            directory: storage_path('framework/cache')
        );

        // expiresAfterはnullでOK（CachedKeySet内の運用で十分）
        return new CachedKeySet(
            $jwksUri,
            $httpClient,
            $httpFactory,
            $cache,
            null,
            true
        );
    }
}
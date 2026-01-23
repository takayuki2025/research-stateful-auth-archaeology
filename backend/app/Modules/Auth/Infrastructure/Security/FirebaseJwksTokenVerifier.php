<?php

namespace App\Modules\Auth\Infrastructure\Security;

use App\Modules\Auth\Domain\Dto\DecodedToken;
use App\Modules\Auth\Domain\Port\TokenVerifierPort;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class FirebaseJwksTokenVerifier implements TokenVerifierPort
{
    private const CERTS_URL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';

    public function __construct(
        private string $projectId,
    ) {}

    public function decode(string $jwt): DecodedToken
    {
        // JWT 形式チェック（2つのドットが必要）
        if (substr_count($jwt, '.') !== 2) {
            throw new \UnexpectedValueException('Firebase token format invalid (must have two dots)');
        }

        [$h64, $p64, $s64] = explode('.', $jwt);

        $header = json_decode($this->b64urlDecode($h64), true);
        if (!is_array($header) || empty($header['kid'])) {
            throw new \UnexpectedValueException('Firebase token header missing kid');
        }
        $kid = (string) $header['kid'];

        $certs = $this->fetchCerts();
        $certPem = $certs[$kid] ?? null;
        if (!$certPem) {
            throw new \UnexpectedValueException('Firebase public key not found for kid');
        }

        // x509 cert から公開鍵を作る
        $pubKey = openssl_pkey_get_public($certPem);
        if ($pubKey === false) {
            throw new \UnexpectedValueException('Failed to parse Firebase x509 cert');
        }
        $details = openssl_pkey_get_details($pubKey);
        if (!is_array($details) || empty($details['key'])) {
            throw new \UnexpectedValueException('Failed to extract public key');
        }

        // 署名検証（RS256）
        $payload = JWT::decode($jwt, new Key($details['key'], 'RS256'));

        // iss/aud の検証（Firebase ID token の必須）
        $iss = (string) ($payload->iss ?? '');
        $aud = (string) ($payload->aud ?? '');
        $expectedIss = 'https://securetoken.google.com/' . $this->projectId;

        if ($iss !== $expectedIss) {
            throw new \UnexpectedValueException("Invalid iss: {$iss}");
        }
        if ($aud !== $this->projectId) {
            throw new \UnexpectedValueException("Invalid aud: {$aud}");
        }

        return new DecodedToken(
            provider: 'firebase',
            payload: $payload
        );
    }

    /** @return array<string,string> kid => x509 pem */
    private function fetchCerts(): array
    {
        // 1時間キャッシュ（Laravel cache が使えるならそれを推奨）
        $cacheFile = sys_get_temp_dir() . '/firebase_certs.json';
        if (is_file($cacheFile) && (time() - filemtime($cacheFile) < 3600)) {
            $json = file_get_contents($cacheFile);
        } else {
            $json = @file_get_contents(self::CERTS_URL);
            if ($json === false) {
                throw new \UnexpectedValueException('Failed to fetch Firebase certs');
            }
            file_put_contents($cacheFile, $json);
        }

        $certs = json_decode($json, true);
        if (!is_array($certs)) {
            throw new \UnexpectedValueException('Invalid certs response');
        }
        return $certs;
    }

    private function b64urlDecode(string $s): string
    {
        $s = strtr($s, '-_', '+/');
        $pad = strlen($s) % 4;
        if ($pad) $s .= str_repeat('=', 4 - $pad);
        return base64_decode($s) ?: '';
    }
}
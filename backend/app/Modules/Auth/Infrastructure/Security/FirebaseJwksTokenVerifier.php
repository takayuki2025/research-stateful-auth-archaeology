<?php

namespace App\Modules\Auth\Infrastructure\Security;

use App\Modules\Auth\Domain\Dto\DecodedToken;
use App\Modules\Auth\Domain\Port\TokenVerifierPort;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class FirebaseJwksTokenVerifier implements TokenVerifierPort
{
    private const CERTS_URL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';

    // 時計ズレ許容（秒）
    private const LEEWAY_SECONDS = 60;

    // certs 取得タイムアウト（秒）
    private const FETCH_TIMEOUT_SECONDS = 5;

    public function __construct(
        private readonly string $projectId,
    ) {}

    public function decode(string $jwt): DecodedToken
    {
        if (substr_count($jwt, '.') !== 2) {
            throw new \UnexpectedValueException('Firebase token format invalid (must have two dots)');
        }

        [$h64] = explode('.', $jwt, 2);
        $header = json_decode($this->b64urlDecode($h64), true);

        if (!is_array($header) || empty($header['kid'])) {
            throw new \UnexpectedValueException('Firebase token header missing kid');
        }
        $kid = (string) $header['kid'];

        // 1) まず cache から
        $certs = $this->fetchCerts(forceRefresh: false);
        $certPem = $certs[$kid] ?? null;

        // 2) kid が無ければ 1回だけ強制リフレッシュして再試行
        if (!$certPem) {
            $certs = $this->fetchCerts(forceRefresh: true);
            $certPem = $certs[$kid] ?? null;
        }

        if (!$certPem) {
            throw new \UnexpectedValueException("Firebase public key not found for kid: {$kid}");
        }

        $pubKey = openssl_pkey_get_public($certPem);
        if ($pubKey === false) {
            throw new \UnexpectedValueException('Failed to parse Firebase x509 cert');
        }
        $details = openssl_pkey_get_details($pubKey);
        if (!is_array($details) || empty($details['key'])) {
            throw new \UnexpectedValueException('Failed to extract public key');
        }

        // leeway を一時的に設定
        $prevLeeway = JWT::$leeway ?? 0;
        JWT::$leeway = self::LEEWAY_SECONDS;

        try {
            $payload = JWT::decode($jwt, new Key($details['key'], 'RS256'));
        } finally {
            JWT::$leeway = $prevLeeway;
        }

        // iss/aud 検証
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
    private function fetchCerts(bool $forceRefresh): array
    {
        $cacheFile = sys_get_temp_dir() . '/firebase_certs_' . md5($this->projectId) . '.json';
        $ttl = 3600;

        if (!$forceRefresh && is_file($cacheFile) && (time() - filemtime($cacheFile) < $ttl)) {
            $json = file_get_contents($cacheFile);
        } else {
            $json = $this->httpGet(self::CERTS_URL);
            file_put_contents($cacheFile, $json);
        }

        $certs = json_decode($json, true);
        if (!is_array($certs)) {
            throw new \UnexpectedValueException('Invalid certs response');
        }
        return $certs;
    }

    private function httpGet(string $url): string
    {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => self::FETCH_TIMEOUT_SECONDS,
                'ignore_errors' => true,
                'header' => "User-Agent: occore-api\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $json = @file_get_contents($url, false, $ctx);
        if ($json === false || $json === '') {
            throw new \UnexpectedValueException('Failed to fetch Firebase certs (timeout or network)');
        }
        return $json;
    }

    private function b64urlDecode(string $s): string
    {
        $s = strtr($s, '-_', '+/');
        $pad = strlen($s) % 4;
        if ($pad) $s .= str_repeat('=', 4 - $pad);
        return base64_decode($s) ?: '';
    }
}
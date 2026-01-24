<?php

namespace App\Modules\Auth\Infrastructure\Security;

use App\Modules\Auth\Domain\Dto\DecodedToken;
use App\Modules\Auth\Domain\Port\TokenVerifierPort;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class FirebaseJwksTokenVerifier implements TokenVerifierPort
{
    private const CERTS_URL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';
    private const LEEWAY_SECONDS = 60;
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
        $headerJson = $this->b64urlDecode($h64);
        if ($headerJson === '') {
            throw new \UnexpectedValueException('Firebase token header decode failed');
        }

        $header = json_decode($headerJson, true);
        if (!is_array($header) || empty($header['kid'])) {
            throw new \UnexpectedValueException('Firebase token header missing kid');
        }
        if (($header['alg'] ?? '') !== 'RS256') {
            throw new \UnexpectedValueException('Firebase token alg must be RS256');
        }

        $kid = (string) $header['kid'];

        $certs = $this->fetchCerts(forceRefresh: false);
        $certPem = $certs[$kid] ?? null;

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

        $prevLeeway = JWT::$leeway ?? 0;
        JWT::$leeway = self::LEEWAY_SECONDS;

        try {
            $payload = JWT::decode($jwt, new Key($details['key'], 'RS256'));
        } finally {
            JWT::$leeway = $prevLeeway;
        }

        $iss = (string) ($payload->iss ?? '');
        $aud = (string) ($payload->aud ?? '');
        $expectedIss = 'https://securetoken.google.com/' . $this->projectId;

        if ($iss !== $expectedIss) {
            throw new \UnexpectedValueException("Invalid iss: {$iss}");
        }
        if ($aud !== $this->projectId) {
            throw new \UnexpectedValueException("Invalid aud: {$aud}");
        }

        return new DecodedToken(provider: 'firebase', payload: $payload);
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
            file_put_contents($cacheFile, $json, LOCK_EX);
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

        $body = @file_get_contents($url, false, $ctx);

        // HTTP status を拾う
        $statusLine = $http_response_header[0] ?? '';
        if (!is_string($statusLine) || $statusLine === '') {
            throw new \UnexpectedValueException('Failed to fetch Firebase certs (no response)');
        }
        if (!str_contains($statusLine, ' 200 ')) {
            throw new \UnexpectedValueException("Failed to fetch Firebase certs ({$statusLine})");
        }

        if ($body === false || $body === '') {
            throw new \UnexpectedValueException('Failed to fetch Firebase certs (timeout or network)');
        }
        return $body;
    }

    private function b64urlDecode(string $s): string
    {
        $s = strtr($s, '-_', '+/');
        $pad = strlen($s) % 4;
        if ($pad) $s .= str_repeat('=', 4 - $pad);
        $decoded = base64_decode($s, true);
        return $decoded === false ? '' : $decoded;
    }
}
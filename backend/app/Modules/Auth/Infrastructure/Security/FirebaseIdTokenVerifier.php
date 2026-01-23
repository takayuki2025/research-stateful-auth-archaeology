<?php

namespace App\Modules\Auth\Infrastructure\Security;

use App\Modules\Auth\Domain\Dto\DecodedToken;
use App\Modules\Auth\Domain\Port\TokenVerifierPort;
use Kreait\Firebase\Factory;

final class FirebaseIdTokenVerifier implements TokenVerifierPort
{
    public function __construct(
        private string $projectId,
        private string $credentialsPath,
    ) {}

    public function decode(string $jwt): DecodedToken
{
    $factory = (new Factory)->withServiceAccount($this->credentialsPath);
    $auth = $factory->createAuth();

    $verifiedToken = $auth->verifyIdToken($jwt);

    $claims = $verifiedToken->claims()->all();

    $aud = $claims['aud'] ?? null;
    $iss = $claims['iss'] ?? null;

    if ($aud !== $this->projectId) {
        throw new \UnexpectedValueException("Firebase aud mismatch: {$aud}");
    }
    $expectedIss = "https://securetoken.google.com/{$this->projectId}";
    if ($iss !== $expectedIss) {
        throw new \UnexpectedValueException("Firebase iss mismatch: {$iss}");
    }

    return new DecodedToken(
        provider: 'firebase',
        payload: (object) $claims
    );
}
}
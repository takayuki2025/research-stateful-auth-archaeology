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
        // Firebase Admin SDK で ID Token を検証（署名/iss/aud/exp）
        $factory = (new Factory)->withServiceAccount($this->credentialsPath);
        $auth = $factory->createAuth();

        $verifiedToken = $auth->verifyIdToken($jwt, $this->projectId);

        // kreait は claims を配列で返せるので object に寄せる
        $claims = $verifiedToken->claims()->all();

        return new DecodedToken(
            provider: 'firebase',
            payload: (object) $claims
        );
    }
}
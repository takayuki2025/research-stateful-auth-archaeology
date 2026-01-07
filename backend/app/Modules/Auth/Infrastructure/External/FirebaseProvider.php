<?php

namespace App\Modules\Auth\Infrastructure\External;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Illuminate\Support\Facades\Log;
use App\Modules\Auth\Application\Dto\ExternalAuthUser;

final class FirebaseProvider
{
    private $auth = null;

    private function getAuth()
    {
        if ($this->auth !== null) {
            return $this->auth;
        }

        $credentials = config('services.firebase.credentials');

        if (! $credentials || ! file_exists($credentials)) {
            throw new \RuntimeException(
                'Firebase service account credentials not found: ' . $credentials
            );
        }

        $factory = (new Factory())
            ->withServiceAccount($credentials)
            ->withProjectId(config('services.firebase.project_id'));

        $this->auth = $factory->createAuth();

        return $this->auth;
    }

    /**
     * Firebase ID Token 検証
     * Infrastructure → Application 境界
     */
    public function verifyToken(string $idToken): ExternalAuthUser
    {
        $auth = $this->getAuth();

        try {
            // ★ 実運用で必須の leeway
            $verifiedToken = $auth->verifyIdToken(
                $idToken,
                $leewayInSeconds = 60
            );
        } catch (FailedToVerifyToken $e) {
            Log::warning('[Firebase verifyToken failed]', [
                'reason' => $e->getMessage(),
            ]);
            throw $e;
        }

        // Firebase UID
        $uid = (string) $verifiedToken->claims()->get('sub');

        // UserRecord 取得（email / verified / name はこっちが SoT）
        $userRecord = $auth->getUser($uid);

        return new ExternalAuthUser(
            uid: $uid,
            email: $userRecord->email ?? null,
            emailVerified: (bool) $userRecord->emailVerified,
            displayName: $userRecord->displayName ?? null,
        );
    }
}

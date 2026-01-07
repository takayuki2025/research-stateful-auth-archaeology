<?php

namespace App\Modules\Auth\Application\Service;

use App\Modules\Auth\Domain\Port\UserProvisioningPort;
use App\Modules\Auth\Domain\Port\TokenVerifierPort;
use App\Modules\Auth\Domain\ValueObject\AuthPrincipal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;

final class JwtUserResolver
{
    public function __construct(
        private TokenVerifierPort $verifier,
        private UserProvisioningPort $provisioning,
    ) {
    }

    public function resolve(Request $request): ?array
    {
        $authHeader = $request->header('Authorization');

        if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = substr($authHeader, 7);

        try {
            // ★ Application は署名方式を一切知らない
            $payload = $this->verifier->decode($token);
        } catch (\Throwable $e) {
            Log::warning('[JwtUserResolver] token verification failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }

        if (! isset($payload->sub)) {
            return null;
        }

        /* =========================================
         * ① DB の事実を確定（Token → ProvisionedUser）
         * ========================================= */
        $provisioned = $this->provisioning->provisionFromJwt(
            userId: (int) $payload->sub
        );

        /* =========================================
         * ② Laravel User（互換レイヤー）
         * ========================================= */
        $eloquentUser = User::find($provisioned->userId);
        if (! $eloquentUser) {
            return null;
        }

        /* =========================================
         * ③ AuthPrincipal（唯一の真実）
         * ========================================= */

        $principal = AuthPrincipal::fromProvisionedUser(
            user: $provisioned,
            provider: 'token',
            providerUid: (string) $payload->sub
        );


        return [
            'user'      => $eloquentUser,
            'principal' => $principal,
        ];
    }
}

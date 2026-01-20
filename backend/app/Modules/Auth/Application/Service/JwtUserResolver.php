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
            $decoded = $this->verifier->decode($token); // DecodedToken
            $payload = $decoded->payload;               // object
            $provider = $decoded->provider;             // string
        } catch (\Throwable $e) {
            Log::warning('[JwtUserResolver] token verification failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }

        if (! isset($payload->sub)) {
            return null;
        }

        $sub = (string) $payload->sub;

        // ✅ 全方式共通（外部ID）
        $provisioned = $this->provisioning->provisionFromExternalIdentity(
            provider: $provider,
            providerUid: $sub,
            email: $payload->email ?? null,
            emailVerified: $payload->email_verified ?? null,
            displayName: $payload->name ?? null,
            claims: (array) $payload,
        );

        // 互換：もし既存トークンが sub=内部user_id の場合
        if (! $provisioned->userId && ctype_digit($sub)) {
            $provisioned = $this->provisioning->provisionFromJwt((int) $sub);
        }

        $eloquentUser = User::find($provisioned->userId);
        if (! $eloquentUser) {
            return null;
        }

        $principal = AuthPrincipal::fromProvisionedUser(
            user: $provisioned,
            provider: $provider,
            providerUid: $sub
        );

\Log::info('[🔥JwtUserResolver] decoded', [
  'provider' => $provider,
  'sub' => $sub,
  'email' => $payload->email ?? null,
]);

        return [
            'user'      => $eloquentUser,
            'principal' => $principal,
        ];
    }
}
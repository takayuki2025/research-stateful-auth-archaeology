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
            $decoded  = $this->verifier->decode($token); // DecodedToken
            $payload  = $decoded->payload;               // object
            $provider = $decoded->provider;              // string
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

        // ------------------------------------------------------------
        // ✅ providerごとに「email等の取り出し」を正規化する
        // ------------------------------------------------------------
        $email = $payload->email ?? null;
        $emailVerified = $payload->email_verified ?? null;
        $displayName = $payload->name ?? null;

        if ($provider === 'auth0') {
            // Action で入れている namespace（audience をそのまま namespace にする運用）
            // 例: AUTH0_AUDIENCE=https://api.occore.local
            $ns = rtrim((string) env('AUTH0_AUDIENCE', ''), '/');

            // namespaced claim を優先して拾う
            $email = $this->claim($payload, "{$ns}/email") ?? $email;
            $emailVerified = $this->claim($payload, "{$ns}/email_verified") ?? $emailVerified;
            $displayName = $this->claim($payload, "{$ns}/name") ?? $displayName;
        }

        // ✅ 全方式共通（外部ID）
        try {
            $provisioned = $this->provisioning->provisionFromExternalIdentity(
                provider: $provider,
                providerUid: $sub,
                email: is_string($email) ? $email : null,
                emailVerified: is_bool($emailVerified) ? $emailVerified : null,
                displayName: is_string($displayName) ? $displayName : null,
                // stdClass の (array) キャストは事故ることがあるので get_object_vars 推奨
                claims: get_object_vars($payload),
            );
        } catch (\Throwable $e) {
            Log::warning('[JwtUserResolver] provisioning failed', [
                'provider' => $provider,
                'sub' => $sub,
                'email' => is_string($email) ? $email : null,
                'error' => $e->getMessage(),
            ]);
            return null;
        }

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

        Log::info('[🔥JwtUserResolver] decoded', [
            'provider' => $provider,
            'sub' => $sub,
            'email' => is_string($email) ? $email : null,
        ]);

        return [
            'user'      => $eloquentUser,
            'principal' => $principal,
        ];
    }

    private function claim(object $payload, string $key): mixed
    {
        // stdClass のプロパティとして namespaced key を取る
        return property_exists($payload, $key) ? $payload->{$key} : null;
    }
}
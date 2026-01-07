<?php

namespace App\Modules\Auth\Domain\Service;

use App\Models\RefreshToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RefreshTokenService
{
    public function __construct(
        private int $ttlDays = 30
    ) {
    }

    /**
     * 新しい RefreshToken を発行し、【生トークン文字列】を戻り値として返す。
     * DB には token_hash のみ保存。
     */
    public function issue(
        User $user,
        ?string $ip = null,
        ?string $ua = null,
        ?string $deviceId = null,
        ?string $deviceName = null,
    ): string {
        // 生トークンを生成
        $rawToken = Str::random(64);
        $hash = hash('sha256', $rawToken);

        RefreshToken::create([
            'user_id'     => $user->id,
            'token_hash'  => $hash,
            'revoked'     => false,
            'expires_at'  => Carbon::now()->addDays($this->ttlDays),
            'device_id'   => $deviceId,
            'device_name' => $deviceName,
            'ip_address'  => $ip,
            'user_agent'  => $ua,
        ]);

        return $rawToken;
    }

    /**
     * 渡された生トークンを検証し、有効な RefreshToken レコードを返す。
     */
    public function validate(string $rawToken): ?RefreshToken
    {
        $hash = hash('sha256', $rawToken);

        /** @var RefreshToken|null $refresh */
        $refresh = RefreshToken::where('token_hash', $hash)->first();

        if (! $refresh) {
            return null;
        }

        if ($refresh->revoked) {
            return null;
        }

        if ($refresh->expires_at->isPast()) {
            return null;
        }

        return $refresh;
    }

    /**
     * Rotation: 旧トークンを revoke し、新しいトークン文字列を返す。
     */
    public function rotate(
        RefreshToken $oldRefresh,
        ?string $ip = null,
        ?string $ua = null,
        ?string $deviceId = null,
        ?string $deviceName = null,
    ): string {
        $oldRefresh->revoked = true;
        $oldRefresh->save();

        return $this->issue(
            $oldRefresh->user,
            $ip,
            $ua,
            $deviceId ?? $oldRefresh->device_id,
            $deviceName ?? $oldRefresh->device_name,
        );
    }

    public function revokeAllForUser(User $user): void
    {
        RefreshToken::where('user_id', $user->id)
            ->update(['revoked' => true]);
    }

    public function issueByUserId(
        int $userId,
        ?string $ip = null,
        ?string $ua = null,
        ?string $deviceId = null,
        ?string $deviceName = null,
    ): string {
        // 生トークンを生成
        $rawToken = \Illuminate\Support\Str::random(64);
        $hash = hash('sha256', $rawToken);

        \App\Models\RefreshToken::create([
            'user_id'     => $userId,
            'token_hash'  => $hash,
            'revoked'     => false,
            'expires_at'  => \Carbon\Carbon::now()->addDays($this->ttlDays),
            'device_id'   => $deviceId,
            'device_name' => $deviceName,
            'ip_address'  => $ip,
            'user_agent'  => $ua,
        ]);

        return $rawToken;
    }
}

<?php

namespace App\Modules\User\Application\Service;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Auth\Domain\Port\UserProvisioningPort;
use App\Modules\Auth\Domain\Dto\ProvisionedUser;
use App\Modules\User\Domain\Repository\ProfileRepository;
use App\Modules\User\Domain\Entity\Profile;

final class UserProvisioningService implements UserProvisioningPort
{
    public function __construct(
        private ProfileRepository $profiles,
    ) {
    }

    public function provisionFromFirebase(
        string $firebaseUid,
        ?string $email,
        bool $emailVerified,
        ?string $displayName,
    ): ProvisionedUser {
        if (! $email) {
            throw new \DomainException('Email is required for user provisioning.');
        }

        return DB::transaction(function () use (
            $firebaseUid,
            $email,
            $emailVerified,
            $displayName
        ) {
            // 既存互換：users.firebase_uid を維持
            $user = User::where('firebase_uid', $firebaseUid)->first()
                ?? User::where('email', $email)->first();

            $isFirstLogin = false;

            if (! $user) {
                $user = User::create([
                    'firebase_uid'      => $firebaseUid,
                    'name'              => $displayName ?? 'User',
                    'email'             => $email,
                    'email_verified_at' => $emailVerified ? now() : null,
                    'first_login_at'    => now(),
                ]);
                $isFirstLogin = true;
            } else {
                $updates = [];

                if (! $user->firebase_uid) {
                    $updates['firebase_uid'] = $firebaseUid;
                } elseif ($user->firebase_uid !== $firebaseUid) {
                    throw new \DomainException('Firebase UID mismatch.');
                }

                if ($emailVerified && ! $user->email_verified_at) {
                    $updates['email_verified_at'] = now();
                }

                if (! $user->first_login_at) {
                    $updates['first_login_at'] = now();
                }

                if ($updates) {
                    $user->update($updates);
                }
            }

            // ✅ 汎用ID（user_identities）にも保存（将来IdaaSでも参照できる）
            $this->upsertUserIdentity(
                userId: $user->id,
                provider: 'firebase',
                providerUid: $firebaseUid,
                email: $email,
                emailVerified: $emailVerified,
                displayName: $displayName,
                claims: []
            );

            $this->ensureProfileExists(
                userId: $user->id,
                displayName: $displayName ?? $user->name
            );

            return $this->buildProvisionedUser($user->id, $isFirstLogin);
        });
    }

    public function provisionFromExternalIdentity(
        string $provider,
        string $providerUid,
        ?string $email = null,
        ?bool $emailVerified = null,
        ?string $displayName = null,
        array $claims = [],
    ): ProvisionedUser {
        return DB::transaction(function () use (
            $provider,
            $providerUid,
            $email,
            $emailVerified,
            $displayName,
            $claims
        ) {
            // 1) まず user_identities から紐付けを探す（最優先）
            $identity = DB::table('user_identities')
                ->where('provider', $provider)
                ->where('provider_uid', $providerUid)
                ->first();

            $user = null;
            $isFirstLogin = false;

            if ($identity) {
                $user = User::find($identity->user_id);
            }

            // 2) identity が無い場合、email があれば email で既存 User を拾う（任意）
            //    ※ ここは運用ポリシー次第だが、移行・統合の観点では有効
            if (! $user && $email) {
                $user = User::where('email', $email)->first();
            }

            // 3) それでも無ければ作成（IdaaS/JWT でも新規登録成立）
            if (! $user) {
                if (! $email) {
                    // email が取れないIdPもあるため、ここは要件次第
                    // ただ、OCCはemail前提が強いので例外にして安全側
                    throw new \DomainException('Email is required for external identity provisioning.');
                }

                $user = User::create([
                    'name'              => $displayName ?? 'User',
                    'email'             => $email,
                    'email_verified_at' => ($emailVerified === true) ? now() : null,
                    'first_login_at'    => now(),
                ]);

                $isFirstLogin = true;
            } else {
                // 既存ユーザー更新（email verified / first_login）
                $updates = [];

                if ($email && $user->email !== $email) {
                    // 運用上ここを許容するかは要件次第。
                    // 安全側：email が変わっている場合は更新しない（別途運用対応）
                    // $updates['email'] = $email;
                }

                if ($emailVerified === true && ! $user->email_verified_at) {
                    $updates['email_verified_at'] = now();
                }

                if (! $user->first_login_at) {
                    $updates['first_login_at'] = now();
                }

                if ($updates) {
                    $user->update($updates);
                }
            }

            // 4) identity を upsert（これが “全方式対応” の核）
            $this->upsertUserIdentity(
                userId: $user->id,
                provider: $provider,
                providerUid: $providerUid,
                email: $email,
                emailVerified: $emailVerified,
                displayName: $displayName,
                claims: $claims,
            );

            // 5) Profile は必ず存在
            $this->ensureProfileExists(
                userId: $user->id,
                displayName: $displayName ?? $user->name
            );

            return $this->buildProvisionedUser($user->id, $isFirstLogin);
        });
    }

    public function provisionFromJwt(int $userId): ProvisionedUser
    {
        return DB::transaction(function () use ($userId) {
            $user = User::find($userId);

            if (! $user) {
                throw new \DomainException('User not found for JWT provisioning.');
            }

            // 互換：既存JWT（sub=内部user_id）を想定
            return $this->buildProvisionedUser($user->id, false);
        });
    }

    /* =========================================================
       Internal helpers
    ========================================================= */

    private function ensureProfileExists(int $userId, string $displayName): void
    {
        $profile = $this->profiles->findByUserId($userId);

        if (! $profile) {
            $this->profiles->save(
                Profile::createEmpty(
                    userId: $userId,
                    displayName: $displayName
                )
            );
        }
    }

    private function buildProvisionedUser(int $userId, bool $isFirstLogin): ProvisionedUser
    {
        $user = User::find($userId);

        $shopIds = DB::table('role_user')
            ->where('user_id', $userId)
            ->pluck('shop_id')
            ->filter()
            ->values()
            ->all();

        $roles = DB::table('role_user')
            ->where('user_id', $userId)
            ->pluck('role_id')
            ->values()
            ->all();

        return new ProvisionedUser(
            userId: $userId,
            email: $user?->email,
            emailVerified: (bool) ($user?->email_verified_at),
            roles: $roles,
            shopIds: $shopIds,
            tenantId: $shopIds[0] ?? null,
            isFirstLogin: $isFirstLogin,
        );
    }

    private function upsertUserIdentity(
        int $userId,
        string $provider,
        string $providerUid,
        ?string $email,
        ?bool $emailVerified,
        ?string $displayName,
        array $claims,
    ): void {
        DB::table('user_identities')->updateOrInsert(
            [
                'provider' => $provider,
                'provider_uid' => $providerUid,
            ],
            [
                'user_id' => $userId,
                'email' => $email,
                'email_verified' => $emailVerified,
                'display_name' => $displayName,
                'claims_json' => $claims ? json_encode($claims, JSON_UNESCAPED_UNICODE) : null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function provisionFromAuth0(
    string $auth0Sub,
    ?string $email,
    bool $emailVerified,
    ?string $displayName,
    array $claims = [],
): ProvisionedUser {
    return $this->provisionFromExternalIdentity(
        provider: 'auth0',
        providerUid: $auth0Sub,
        email: $email,
        emailVerified: $emailVerified,
        displayName: $displayName,
        claims: $claims,
    );
}
}
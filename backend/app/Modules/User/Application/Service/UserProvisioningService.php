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

            /** ---------------------------------------
             * User SoT（users テーブル）
             * -------------------------------------- */
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

            /** ---------------------------------------
             * Profile SoT（profiles テーブル）
             * ★ 必ず存在させる
             * -------------------------------------- */
            $profile = $this->profiles->findByUserId($user->id);

            if (! $profile) {
                $this->profiles->save(
                    Profile::createEmpty(
                        userId: $user->id,
                        displayName: $displayName ?? $user->name
                    )
                );
            }

            /** ---------------------------------------
             * Role / Shop 情報
             * -------------------------------------- */
            $shopIds = DB::table('role_user')
                ->where('user_id', $user->id)
                ->pluck('shop_id')
                ->filter()
                ->values()
                ->all();

            $roles = DB::table('role_user')
                ->where('user_id', $user->id)
                ->pluck('role_id')
                ->values()
                ->all();

            return new ProvisionedUser(
                userId: $user->id,
                email: $user->email,
                emailVerified: $user->email_verified_at !== null,
                roles: $roles,
                shopIds: $shopIds,
                tenantId: $shopIds[0] ?? null,
                isFirstLogin: $isFirstLogin,
            );
        });
    }

    public function provisionFromJwt(int $userId): ProvisionedUser
    {
        return DB::transaction(function () use ($userId) {

            $user = User::find($userId);

            if (! $user) {
                throw new \DomainException('User not found for JWT provisioning.');
            }

            $shopIds = DB::table('role_user')
                ->where('user_id', $user->id)
                ->pluck('shop_id')
                ->filter()
                ->values()
                ->all();

            $roles = DB::table('role_user')
                ->where('user_id', $user->id)
                ->pluck('role_id')
                ->values()
                ->all();

            return new ProvisionedUser(
                userId: $user->id,
                email: $user->email,
                roles: $roles,
                shopIds: $shopIds,
                tenantId: $shopIds[0] ?? null,
                isFirstLogin: false,
                emailVerified: (bool) $user->email_verified_at,
            );
        });
    }
}

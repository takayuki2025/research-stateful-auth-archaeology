<?php

namespace App\Modules\Auth\Domain\Port;

use App\Modules\Auth\Domain\Dto\ProvisionedUser;

interface UserProvisioningPort
{
    public function provisionFromFirebase(
        string $firebaseUid,
        ?string $email,
        bool $emailVerified,
        ?string $displayName,
    ): ProvisionedUser;

    /**
     * ✅ 全方式共通：OIDC/JWT の sub（多くは string）で User を確定
     */
    public function provisionFromExternalIdentity(
        string $provider,        // 'firebase' | 'auth0' | 'cognito' | 'custom' | 'token'
        string $providerUid,     // sub
        ?string $email = null,
        ?bool $emailVerified = null,
        ?string $displayName = null,
        array $claims = [],
    ): ProvisionedUser;

    /**
     * ✅ 互換維持（既存JWT: sub=内部user_id）
     */
    public function provisionFromJwt(int $userId): ProvisionedUser;
}
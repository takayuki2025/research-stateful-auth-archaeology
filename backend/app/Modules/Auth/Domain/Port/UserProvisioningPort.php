<?php

namespace App\Modules\Auth\Domain\Port;

use App\Modules\Auth\Domain\Dto\ProvisionedUser;

interface UserProvisioningPort
{
    /**
     * Firebase ID Token からの初期プロビジョニング
     */
    public function provisionFromFirebase(
        string $firebaseUid,
        ?string $email,
        bool $emailVerified,
        ?string $displayName,
    ): ProvisionedUser;

    /**
     * ✅ JWT（＝すでに User が存在する前提）
     */
    public function provisionFromJwt(
        int $userId
    ): ProvisionedUser;
}

<?php

namespace App\Modules\User\Domain\Repository;

use App\Modules\User\Domain\Entity\Profile;
use App\Modules\User\Domain\Entity\UserAddress;

interface UserAddressRepository
{
    public function findPrimaryByUser(int $userId): ?UserAddress;

    /**
     * Profile から primary address を作成（初回のみ）
     */
    public function createPrimaryFromProfile(
        int $userId,
        Profile $profile
    ): UserAddress;
}

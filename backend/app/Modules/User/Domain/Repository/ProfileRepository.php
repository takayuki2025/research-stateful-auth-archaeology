<?php

namespace App\Modules\User\Domain\Repository;

use App\Modules\User\Domain\Entity\Profile;

interface ProfileRepository
{
    public function findByUserId(int $userId): ?Profile;
    public function save(Profile $profile): Profile;
}

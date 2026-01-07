<?php

namespace App\Modules\Auth\Domain\Service;

use App\Models\User;
use App\Modules\Auth\Domain\Entity\AuthUser;

class AuthUserFactory
{
    public function fromEloquent(User $user): AuthUser
    {
        return new AuthUser(
            id: $user->id,
            name: $user->name,
            email: $user->email,
        );
    }
}

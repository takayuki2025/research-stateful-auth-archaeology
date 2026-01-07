<?php

namespace App\Modules\Auth\Infrastructure\Persistence;

use App\Models\User;
use App\Modules\Auth\Domain\Repository\AuthUserRepositoryInterface;

class AuthUserRepository implements AuthUserRepositoryInterface
{
    public function findByFirebaseUid(string $uid): ?User
    {
        return User::where('firebase_uid', $uid)->first();
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function save(User $user): User
    {
        $user->save();
        return $user;
    }
}

<?php

namespace App\Modules\User\Infrastructure\Persistence\Repository;

use App\Models\User;
use App\Modules\Auth\Domain\ValueObject\AuthPrincipal;
use App\Modules\User\Domain\Repository\UserRepository;

final class EloquentUserRepository implements UserRepository
{
    public function findById(int $userId): ?User
    {
    return User::find($userId);
    }

    public function findByAuthUid(string $uid): ?User
    {
        return User::where('auth_uid', $uid)->first();
    }

    public function createFromPrincipal(AuthPrincipal $principal): User
    {
        return User::create([
            'auth_uid' => $principal->uid(),
            'email'    => $principal->email(),
            'name'     => $principal->name() ?? 'Guest',
        ]);
    }
}

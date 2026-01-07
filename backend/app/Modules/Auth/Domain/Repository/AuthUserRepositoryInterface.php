<?php

namespace App\Modules\Auth\Domain\Repository;

use App\Models\User;

interface AuthUserRepositoryInterface
{
    public function findByFirebaseUid(string $uid): ?User;
    public function findByEmail(string $email): ?User;
    public function save(User $user): User;
}

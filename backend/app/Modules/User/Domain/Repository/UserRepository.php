<?php

namespace App\Modules\User\Domain\Repository;

use App\Models\User;
use App\Modules\Auth\Domain\ValueObject\AuthPrincipal;

interface UserRepository
{

    public function findById(int $userId): ?User;

    public function findByAuthUid(string $uid): ?User;

    public function createFromPrincipal(AuthPrincipal $principal): User;
}
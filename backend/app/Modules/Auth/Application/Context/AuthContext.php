<?php

namespace App\Modules\Auth\Application\Context;

use App\Modules\Auth\Domain\ValueObject\AuthPrincipal;

final class AuthContext
{
    private ?AuthPrincipal $principal = null;

    public function setPrincipal(AuthPrincipal $principal): void
    {
        $this->principal = $principal;
    }

    public function clear(): void
    {
        $this->principal = null;
    }

    public function principal(): AuthPrincipal
    {
        if ($this->principal === null) {
            throw new \RuntimeException('AuthPrincipal not set');
        }

        return $this->principal;
    }

    public function hasPrincipal(): bool
    {
        return $this->principal !== null;
    }

    public function principalOrNull(): ?AuthPrincipal
    {
        return $this->principal;
    }


    /**
     * ガード用途（任意）
     */
    public function isAuthenticated(): bool
    {
        return $this->principal !== null;
    }
}

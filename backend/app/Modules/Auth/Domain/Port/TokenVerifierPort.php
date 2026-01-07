<?php

namespace App\Modules\Auth\Domain\Port;

interface TokenVerifierPort
{
    /**
     * @return object decoded JWT payload
     * @throws \Throwable on invalid token
     */
    public function decode(string $jwt): object;
}

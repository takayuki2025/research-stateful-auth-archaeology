<?php

namespace App\Modules\Auth\Application\Dto;

final class LoginOrRegisterOutput
{
    public function __construct(
        public string $token,
        public array $user,
        public string $status,
        public bool $needsEmailVerification,
        public string $refreshToken,
        public bool $isFirstLogin,
    ) {
    }

    public function toArray(): array
    {
        return [
            'token'                  => $this->token,
            'user'                   => $this->user,
            'status'                 => $this->status,
            'needsEmailVerification' => $this->needsEmailVerification,
            'refresh_token'          => $this->refreshToken,
            'isFirstLogin'           => $this->isFirstLogin,
        ];
    }
}

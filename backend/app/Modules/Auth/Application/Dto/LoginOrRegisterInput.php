<?php

namespace App\Modules\Auth\Application\Dto;

class LoginOrRegisterInput
{
    public function __construct(
        public readonly string $firebaseIdToken,
        public readonly ?string $displayName
    ) {
    }
}

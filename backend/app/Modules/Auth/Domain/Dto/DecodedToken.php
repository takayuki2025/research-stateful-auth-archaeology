<?php

namespace App\Modules\Auth\Domain\Dto;

final class DecodedToken
{
    public function __construct(
        public readonly string $provider, // 'firebase' | 'auth0' | 'cognito' | 'custom'
        public readonly object $payload,
    ) {}
}
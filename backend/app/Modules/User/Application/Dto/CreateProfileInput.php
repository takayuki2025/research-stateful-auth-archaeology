<?php

namespace App\Modules\User\Application\Dto;

final class CreateProfileInput
{
    public function __construct(
        public readonly string $displayName,
        public readonly ?string $postNumber,
        public readonly ?string $address,
        public readonly ?string $building,
    ) {
    }
}

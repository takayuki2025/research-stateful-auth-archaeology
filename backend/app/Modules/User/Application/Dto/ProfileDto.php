<?php

namespace App\Modules\User\Application\Dto;

use App\Modules\User\Domain\Entity\Profile;

final class ProfileDto
{
    public function __construct(
        public string $displayName,
        public ?string $postNumber,
        public ?string $address,
        public ?string $building,
        public ?string $userImage,
    ) {}

    public static function fromEntity(Profile $profile): self
    {
        return new self(
            displayName: $profile->displayName(),
            postNumber: $profile->postNumber(),
            address: $profile->address(),
            building: $profile->building(),
            userImage: $profile->userImage(),
        );
    }

    public function toArray(): array
    {
        return [
            'display_name' => $this->displayName,
            'post_number'  => $this->postNumber,
            'address'      => $this->address,
            'building'     => $this->building,
            'user_image'   => $this->userImage,
        ];
    }
}
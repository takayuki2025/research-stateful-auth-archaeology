<?php

namespace App\Modules\User\Application\UseCase;

use App\Modules\User\Application\Dto\ProfileDto;
use App\Modules\User\Domain\Repository\ProfileRepository;


final class GetProfileUseCase
{
    public function __construct(
        private ProfileRepository $profiles,
    ) {
    }

    public function handle(int $userId): ?ProfileDto
    {
        $profile = $this->profiles->findByUserId($userId);

        if (! $profile) {
            return null;
        }

        return ProfileDto::fromEntity($profile);
    }
}

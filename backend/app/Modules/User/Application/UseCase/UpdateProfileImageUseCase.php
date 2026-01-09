<?php

namespace App\Modules\User\Application\UseCase;

use App\Modules\User\Application\Dto\ProfileDto;
use App\Modules\User\Domain\Exception\ProfileNotFoundException;
use App\Modules\User\Domain\Port\ShopAddressSyncPort;
use App\Modules\User\Domain\Repository\ProfileRepository;

final class UpdateProfileImageUseCase
{
    public function __construct(
        private ProfileRepository $profiles,
        private ShopAddressSyncPort $shopSync,
    ) {
    }

    public function handle(int $userId, string $storagePath): ProfileDto
    {
        $current = $this->profiles->findByUserId($userId);
        if (! $current) {
            throw new ProfileNotFoundException();
        }

        $saved = $this->profiles->save(
            $current->withImage($storagePath)
        );

        $this->shopSync->syncFromUserProfile($userId);

        return ProfileDto::fromEntity($saved);
    }
}

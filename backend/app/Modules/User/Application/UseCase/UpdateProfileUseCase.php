<?php

namespace App\Modules\User\Application\UseCase;

use App\Modules\User\Application\Dto\ProfileDto;
use App\Modules\User\Application\Dto\UpdateProfileInput;
use App\Modules\User\Domain\Exception\ProfileNotFoundException;
use App\Modules\User\Domain\Port\ShopAddressSyncPort;
use App\Modules\User\Domain\Repository\ProfileRepository;
use App\Modules\User\Domain\Repository\UserAddressRepository;
use App\Modules\User\Domain\Repository\UserRepository; // ★追加

final class UpdateProfileUseCase
{
    public function __construct(
        private ProfileRepository $profiles,
        private UserAddressRepository $addresses,
        private ShopAddressSyncPort $shopSync,
        private UserRepository $users,
    ) {
    }

    public function handle(int $userId, UpdateProfileInput $input): ProfileDto
    {
        $current = $this->profiles->findByUserId($userId);
        if (! $current) {
            throw new ProfileNotFoundException();
        }

        $next = $current->withBasic(
            displayName: $input->displayName,
            postNumber: $input->postNumber,
            address: $input->address,
            building: $input->building,
        );

        if ($current->equalsBasic($next)) {
            return ProfileDto::fromEntity($current);
        }

        $saved = $this->profiles->save($next);

        // primary address
        $primary = $this->addresses->findPrimaryByUser($userId);
        if (! $primary && $saved->postNumber() && $saved->address()) {
            $this->addresses->createPrimaryFromProfile($userId, $saved);
        }

        $this->shopSync->syncFromUserProfile($userId);

        // ★ User 更新は Repository 経由で行う
        if ($saved->postNumber() && $saved->address()) {
            $this->users->markProfileCompleted($userId);
        }

        if ($saved->displayName()) {
            $this->users->updateDisplayName($userId, $saved->displayName());
        }

        return ProfileDto::fromEntity($saved);
    }
}
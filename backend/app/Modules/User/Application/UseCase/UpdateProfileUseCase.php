<?php

namespace App\Modules\User\Application\UseCase;

use App\Modules\User\Application\Dto\ProfileDto;
use App\Modules\User\Application\Dto\UpdateProfileInput;
use App\Modules\User\Domain\Exception\ProfileNotFoundException;
use App\Modules\User\Domain\Port\ShopAddressSyncPort;
use App\Modules\User\Domain\Repository\ProfileRepository;
use App\Modules\User\Domain\Repository\UserAddressRepository;

final class UpdateProfileUseCase
{
    public function __construct(
        private ProfileRepository $profiles,
        private UserAddressRepository $addresses,
        private ShopAddressSyncPort $shopSync,
    ) {
    }

    public function handle(int $userId, UpdateProfileInput $input): ProfileDto
    {
        $current = $this->profiles->findByUserId($userId);
        if (! $current) {
            // ここで「勝手に createEmpty」は絶対にしない
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

        // primary address 自動生成（既存仕様踏襲）
        $primary = $this->addresses->findPrimaryByUser($userId);
        if (! $primary && $saved->postNumber() && $saved->address()) {
            $this->addresses->createPrimaryFromProfile($userId, $saved);
        }

        $this->shopSync->syncFromUserProfile($userId);

        return ProfileDto::fromEntity($saved);
    }
}

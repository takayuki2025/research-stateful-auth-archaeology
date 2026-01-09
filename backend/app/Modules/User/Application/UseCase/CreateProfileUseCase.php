<?php

namespace App\Modules\User\Application\UseCase;

use App\Modules\User\Application\Dto\CreateProfileInput;
use App\Modules\User\Application\Dto\ProfileDto;
use App\Modules\User\Domain\Entity\Profile;
use App\Modules\User\Domain\Exception\ProfileAlreadyExistsException;
use App\Modules\User\Domain\Port\ShopAddressSyncPort;
use App\Modules\User\Domain\Repository\ProfileRepository;
use App\Modules\User\Domain\Repository\UserAddressRepository;
use App\Modules\User\Domain\Repository\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class CreateProfileUseCase
{
    public function __construct(
        private ProfileRepository $profiles,
        private UserAddressRepository $addresses,
        private ShopAddressSyncPort $shopSync,
        private UserRepository $users, // ★追加
    ) {
    }

    public function handle(int $userId, CreateProfileInput $input): ProfileDto
    {
        return DB::transaction(function () use ($userId, $input) {

            $existing = $this->profiles->findByUserId($userId);
            if ($existing) {
                throw new ProfileAlreadyExistsException();
            }

            $profile = Profile::createEmpty($userId, $input->displayName)
                ->withBasic(
                    displayName: $input->displayName,
                    postNumber: $input->postNumber,
                    address: $input->address,
                    building: $input->building,
                );

            $saved = $this->profiles->save($profile);

            // ★ ここで User を更新
            if ($saved->postNumber() && $saved->address()) {
                $this->users->markProfileCompleted($userId);
            }

            $primary = $this->addresses->findPrimaryByUser($userId);
            if (! $primary && $saved->postNumber() && $saved->address()) {
                $this->addresses->createPrimaryFromProfile($userId, $saved);
            }

            $this->shopSync->syncFromUserProfile($userId);

            return ProfileDto::fromEntity($saved);
        });
    }
}
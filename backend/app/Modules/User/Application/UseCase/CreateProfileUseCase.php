<?php

namespace App\Modules\User\Application\UseCase;

use App\Modules\User\Application\Dto\CreateProfileInput;
use App\Modules\User\Application\Dto\ProfileDto;
use App\Modules\User\Domain\Entity\Profile;
use App\Modules\User\Domain\Exception\ProfileAlreadyExistsException;
use App\Modules\User\Domain\Port\ShopAddressSyncPort;
use App\Modules\User\Domain\Repository\ProfileRepository;
use App\Modules\User\Domain\Repository\UserAddressRepository;

use Illuminate\Support\Facades\Log;

final class CreateProfileUseCase
{
    public function __construct(
        private ProfileRepository $profiles,
        private UserAddressRepository $addresses,
        private ShopAddressSyncPort $shopSync,
    ) {
    }

    public function handle(int $userId, CreateProfileInput $input): ProfileDto
    {
        
Log::warning('[PROFILE][CREATE][ENTER]', [
    'user_id' => $userId,
    'input' => [
        'displayName' => $input->displayName,
        'postNumber'  => $input->postNumber,
        'address'     => $input->address,
        'building'    => $input->building,
    ],
    'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
]);

        $existing = $this->profiles->findByUserId($userId);
        if ($existing) {
            throw new ProfileAlreadyExistsException();
        }

        // Profile::createEmpty(userId, displayName) が既にある前提で組み立てる
        $profile = Profile::createEmpty($userId, $input->displayName);

        $profile = $profile->withBasic(
            displayName: $input->displayName,
            postNumber: $input->postNumber,
            address: $input->address,
            building: $input->building,
        );

        $saved = $this->profiles->save($profile);

        // primary address 自動生成（既存仕様踏襲）
        $primary = $this->addresses->findPrimaryByUser($userId);
        if (! $primary && $saved->postNumber() && $saved->address()) {
            $this->addresses->createPrimaryFromProfile($userId, $saved);
        }

        $this->shopSync->syncFromUserProfile($userId);

        return ProfileDto::fromEntity($saved);
    }
}

<?php

namespace App\Modules\Auth\Application\UseCase;

use App\Modules\Auth\Application\Service\AuthContext;
use App\Modules\Auth\Application\Dto\AuthUserDto;
use App\Modules\Shop\Domain\Repository\ShopRoleQueryRepository;
use App\Modules\User\Domain\Entity\Profile;
use App\Modules\User\Domain\Repository\ProfileRepository;


final class GetMyProfileUseCase
{
    public function __construct(
        private AuthContext $authContext,
        private ProfileRepository $profiles,
        private ShopRoleQueryRepository $shopRoles,
    ) {
    }

    public function handle(): AuthUserDto
    {
        $principal = $this->authContext->principal();

        // 🔵 取得のみ
        $profile = $this->profiles->findByUserId($principal->userId);

        $hasProfile = ($profile !== null);

        $roles = $this->shopRoles->findByUserId($principal->userId);

        return AuthUserDto::fromPrincipalWithProfile(
            principal: $principal,
            profile: $profile,      // null のまま返す
            shopRoles: $roles,
            hasProfile: $hasProfile,
        );
    }
}

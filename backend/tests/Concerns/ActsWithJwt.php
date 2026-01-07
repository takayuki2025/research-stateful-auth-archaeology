<?php

namespace Tests\Concerns;

use App\Models\User;
use App\Modules\Auth\Domain\Service\TokenIssuerService;
use App\Modules\Auth\Domain\Dto\ProvisionedUser;

trait ActsWithJwt
{
    protected function actingAsJwt(User $user): self
    {
        $issuer = app(TokenIssuerService::class);


        $jwt = $issuer->issue(new ProvisionedUser(
            userId: $user->id,
            email: $user->email,
            externalId: 'test_uid',
            roles: ['user'],
            tenantId: null,
            isFirstLogin: false,
            emailVerified: true,
        ));


        return $this->withHeader('Authorization', 'Bearer '.$jwt);
    }
}

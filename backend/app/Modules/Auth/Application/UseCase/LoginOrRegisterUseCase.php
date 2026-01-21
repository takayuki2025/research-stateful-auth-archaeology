<?php

namespace App\Modules\Auth\Application\UseCase;

use App\Modules\Auth\Application\Dto\LoginOrRegisterInput;
use App\Modules\Auth\Application\Dto\LoginOrRegisterOutput;
use App\Modules\Auth\Application\Dto\ExternalAuthUser;
use App\Modules\Auth\Domain\Port\UserProvisioningPort;
use App\Modules\Auth\Domain\Service\TokenIssuerService;
use App\Modules\Auth\Domain\ValueObject\AuthPrincipal;
use App\Modules\Auth\Infrastructure\External\FirebaseProvider;

final class LoginOrRegisterUseCase
{
    public function __construct(
        private FirebaseProvider $firebase,
        private UserProvisioningPort $provisioning,
        private TokenIssuerService $tokenIssuer,
    ) {
    }

    public function handle(LoginOrRegisterInput $input): LoginOrRegisterOutput
    {
        /* =========================================
         * 1. External IDP 認証（Firebase）
         * ========================================= */
        $externalUser = $this->firebase->verifyToken(
            $input->firebaseIdToken
        );
        // ↑ ExternalAuthUser（DTO）

        /* =========================================
         * 2. User Provisioning（SoT 確定）
         * ========================================= */
        $provisioned = $this->provisioning->provisionFromFirebase(
            firebaseUid: $externalUser->uid,
            email: $externalUser->email,
            emailVerified: $externalUser->emailVerified,
            displayName: $externalUser->displayName,
        );

        /* =========================================
         * 3. AuthPrincipal 生成
         * ========================================= */

        $principal = AuthPrincipal::fromProvisionedUser(
            $provisioned,          // ProvisionedUser
            'firebase',             // provider
            $externalUser->uid,     // providerUid
            $externalUser->displayName
        );


        /* =========================================
         * 4. JWT 発行
         * ========================================= */


        $accessToken = $this->tokenIssuer->issue(
            $provisioned,   // ① ProvisionedUser
            $principal      // ② AuthPrincipal
        );



        /* =========================================
         * 5. Output
         * ========================================= */
        return new LoginOrRegisterOutput(
            token: $accessToken,
            user: [
                'id'                => $provisioned->userId,
                'email'             => $provisioned->email,
                'email_verified_at' => $provisioned->emailVerified
                    ? now()->toISOString()
                    : null,
            ],
            status: 'ok',
            needsEmailVerification: ! $provisioned->emailVerified,
            refreshToken: '',
            isFirstLogin: $provisioned->isFirstLogin,
        );
    }
}
<?php

namespace App\Modules\Auth\Application\Dto;

/**
 * 外部 IDP 認証結果の共通表現
 *（Firebase / Auth0 / Cognito 共通）
 */
final class ExternalAuthUser
{
    public function __construct(
        public readonly string $uid,
        public readonly ?string $email,
        public readonly bool $emailVerified,
        public readonly ?string $displayName,
    ) {
    }
}

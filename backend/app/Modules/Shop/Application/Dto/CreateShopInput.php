<?php

namespace App\Modules\Shop\Application\Dto;

final class CreateShopInput
{
    public function __construct(
        public readonly int $ownerUserId,
        public readonly string $name,

        // ★ 発送元住所（Aフェーズ必須）
        public readonly string $postalCode,
        public readonly string $prefecture,
        public readonly string $city,
        public readonly string $addressLine1,
        public readonly ?string $addressLine2 = null,
        public readonly ?string $recipientName = null,
        public readonly ?string $phone = null,
    ) {
    }
}

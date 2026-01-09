<?php

namespace App\Modules\Item\Domain\Service;

use App\Modules\Item\Domain\ValueObject\SellerId;
use App\Modules\Auth\Domain\ValueObject\AuthPrincipal;
use App\Modules\Item\Domain\ValueObject\SellerType;

final class SellerAuthorizationService
{
    public function canOperate(
        SellerId $sellerId,
        AuthPrincipal $principal,
    ): bool {
        return match ($sellerId->type()) {

            // 個人出品
            SellerType::INDIVIDUAL =>
                $sellerId->id() !== null
                && $principal->userId() === $sellerId->id(),

            // 店舗出品
            SellerType::SHOP =>
                $this->canOperateShop($sellerId, $principal),
        };
    }

    private function canOperateShop(
        SellerId $sellerId,
        AuthPrincipal $principal,
    ): bool {
        // Draft フェーズ（shop:managed）
        if ($sellerId->id() === null) {
            return ! empty($principal->shopIds);
        }

        // Publish 後（shop:ID）
        return in_array(
            $sellerId->id(),
            $principal->shopIds,
            true
        );
    }
}

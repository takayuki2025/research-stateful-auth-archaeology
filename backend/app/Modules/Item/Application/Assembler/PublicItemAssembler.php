<?php

namespace App\Modules\Item\Application\Assembler;

use App\Models\Item as EloquentItem;
use App\Modules\Item\Application\Dto\Item\PublicItemDto;
use Carbon\Carbon;

final class PublicItemAssembler
{
    public static function fromReadModel(
        array $row,
        ?int $viewerUserId,
        array $viewerShopIds,
        bool $isFavorited,
        int $favoritesCount,
    ): PublicItemDto {

        $itemId = (int) $row['id'];
        $shopId = $row['shop_id'] ?? null;
        $createdByUserId = $row['created_by_user_id'] ?? null;

        $isOwner = $viewerUserId !== null
            && $createdByUserId === $viewerUserId;

        $belongsToAnyShop = !empty($viewerShopIds);

        $canManage = $shopId !== null
            && in_array($shopId, $viewerShopIds, true);

        // =========================
        // ⭐️ / 💫 表示ルール（修正版）
        // =========================

        $displayType = null;

        /**
         * ⭐️ ショップ所属ユーザーの個人出品
         * - owner であっても
         * - shop_id === null
         * - viewer が shop に所属している
         */
        if (
            $shopId === null
            && $isOwner
            && $belongsToAnyShop
        ) {
            $displayType = 'STAR';
        }

        /**
         * 💫 一般ユーザーの個人出品
         */
        elseif (
            $shopId === null
            && $isOwner
            && !$belongsToAnyShop
        ) {
            $displayType = 'MY_ITEM';
        }

        /**
         * ❤️ FAVORITE は最優先
         */
        if ($isFavorited) {
            $displayType = 'FAVORITE';
        }

        return new PublicItemDto(
            id: $itemId,
            name: (string) $row['name'],
            price: (int) $row['price'],
            itemImagePath: $row['item_image']
                ? '/storage/' . ltrim($row['item_image'], '/')
                : null,
            brandPrimary: $row['brand'] ?? null,
            conditionName: $row['condition'] ?? null,
            colorName: null,
            publishedAt: $row['published_at']
                ? Carbon::parse($row['published_at'])->toISOString()
                : null,
            displayType: $displayType,
            isOwner: $isOwner,
            canManage: $canManage,
            isFavorited: $isFavorited,
            favoritesCount: $favoritesCount,
        );
    }
}

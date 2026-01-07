<?php

namespace App\Modules\Item\Domain\Service;

use App\Models\User;

final class ViewerShopResolver
{
    /**
     * 公開カタログ閲覧時に
     * 「このユーザーの出品を除外する shop_id」を返す
     */
    public function resolveForPublicCatalog(?User $user): ?int
    {
        if (! $user) {
            return null; // 未ログイン → 全件表示
        }

        // ① 互換用（存在するなら最優先）
        if ($user->shop_id) {
            return (int) $user->shop_id;
        }

        // ② role_user 経由（最初の shop）
        $shopId = $user->shops()->value('shops.id');

        return $shopId ? (int) $shopId : null;
    }
}

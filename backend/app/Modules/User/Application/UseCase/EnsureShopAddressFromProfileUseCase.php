<?php

namespace App\Modules\User\Application\UseCase;

use App\Modules\User\Domain\Port\ShopAddressSyncPort;

/**
 * EnsureShopAddressFromProfileUseCase
 *
 * Occ_User_v1 方針：
 * - User Profile 更新後に Shop 側 address の整合性を保証する
 * - User は Shop の実装詳細を一切知らない
 * - 同期処理は Port 経由で委譲する
 *
 * 将来：
 * - Domain Event（UserProfileUpdated）に置き換え可能
 */
final class EnsureShopAddressFromProfileUseCase
{
    public function __construct(
        private ShopAddressSyncPort $port
    ) {
    }

    /**
     * User Profile を起点に Shop Address を同期する
     */
    public function handle(int $userId): void
    {
        $this->port->syncFromUserProfile($userId);
    }
}

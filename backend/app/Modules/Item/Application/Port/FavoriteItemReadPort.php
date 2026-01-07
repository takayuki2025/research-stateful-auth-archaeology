<?php

namespace App\Modules\Item\Application\Port;

interface FavoriteItemReadPort
{
    /**
     * お気に入り一覧表示用（ReadModel）
     *
     * @return array<int, array<string, mixed>>
     */
    public function listByUserId(int $userId): array;
}

<?php

namespace App\Modules\Item\Infrastructure\Persistence\Query;

use Illuminate\Support\Facades\DB;

final class FavoriteItemReadRepository
{
    /**
     * @return array<int, array<string,mixed>>
     */
    public function listByUserId(
        int $userId,
        int $limit,
        int $page
    ): array {
        $offset = ($page - 1) * $limit;

        // goods(or favorites) テーブル名は FavoriteRepository 実装に合わせて調整してください。
        // ここでは goods を想定します。
        $rows = DB::table('goods')
            ->join('items', 'goods.item_id', '=', 'items.id')
            ->where('goods.user_id', $userId)
            ->whereNotNull('items.published_at')
            ->orderByDesc('goods.id')
            ->limit($limit)
            ->offset($offset)
            ->select([
                'items.id',
                'items.shop_id',
                'items.created_by_user_id',
                'items.name',
                'items.price',
                'items.item_image',
                'items.brand',
                'items.condition',
                'items.published_at',
            ])
            ->get();


            $analysis = DB::table('analysis_results')
    ->where('item_id', $itemId)
    ->where('status', 'active')
    ->latest('id')
    ->first();

        return $rows->map(fn ($r) => (array)$r)->all();
    }
}

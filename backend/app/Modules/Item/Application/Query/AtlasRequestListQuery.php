<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Query;

use Illuminate\Support\Facades\DB;

final class AtlasRequestListQuery
{
    public function listByShopCode(string $shopCode): array
    {
        return DB::table('analysis_requests')
            ->join('items', 'items.id', '=', 'analysis_requests.item_id')
            ->join('shops', 'shops.id', '=', 'items.shop_id')
            ->where('shops.shop_code', $shopCode)
            ->orderByDesc('analysis_requests.created_at')
            ->select([
                'analysis_requests.id',
                'analysis_requests.item_id',
                'analysis_requests.status',
                'analysis_requests.analysis_version',
                'analysis_requests.created_at',
            ])
            ->get()
            ->toArray();
    }
}
<?php

namespace App\Modules\Item\Infrastructure\Persistence\Query;

use Illuminate\Support\Facades\DB;

final class AnalysisResultReadRepository
{
    /**
     * 最新の active な解析結果を取得
     */
    public function findLatestActiveByItemId(int $itemId): ?array
    {
        $row = DB::table('analysis_results')
            ->where('item_id', $itemId)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();

        if (! $row) {
            return null;
        }

        return json_decode($row->payload, true);
    }
}
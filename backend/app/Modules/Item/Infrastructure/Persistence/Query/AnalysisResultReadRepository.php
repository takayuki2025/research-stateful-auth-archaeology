<?php

namespace App\Modules\Item\Infrastructure\Persistence\Query;

use Illuminate\Support\Facades\DB;

final class AnalysisResultReadRepository
{
    public function findLatestActiveByItemId(int $itemId): ?array
    {
        $row = DB::table('analysis_results')
            ->where('item_id', $itemId)
            ->whereIn('status', ['active', 'provisional'])
            ->orderByDesc('id')
            ->first();

        if (! $row) {
            return null;
        }

        $display = [];

        if ($row->brand_name) {
            $display['brand'] = [
                'name'   => $row->brand_name,
                'source' => $row->source,
            ];
        }

        if ($row->condition_name) {
            $display['condition'] = [
                'name' => $row->condition_name,
            ];
        }

        if ($row->color_name) {
            $display['color'] = [
                'name' => $row->color_name,
            ];
        }

        if ($row->confidence_map) {
            $display['confidence_map'] = json_decode($row->confidence_map, true);
        }

        return $display ?: null;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ItemEntity;

class EntityKpiController extends Controller
{
    public function __invoke()
    {
        $total = ItemEntity::where('is_latest', true)->count();

        $needsReview = ItemEntity::where('is_latest', true)
            ->where('decision', 'needs_review')
            ->count();

        $autoAccept = ItemEntity::where('is_latest', true)
            ->where('decision', 'auto_accept')
            ->count();

        $rejected = ItemEntity::where('is_latest', true)
            ->where('decision', 'rejected')
            ->count();

        return [
            'total' => $total,
            'needs_review' => $needsReview,
            'auto_accept' => $autoAccept,
            'rejected' => $rejected,
            'approval_rate' => $total
                ? round($autoAccept / $total * 100, 2)
                : 0,
        ];
    }
}

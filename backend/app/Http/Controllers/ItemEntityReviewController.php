<?php

namespace App\Http\Controllers;

use App\Models\ItemEntity;
use App\Jobs\AnalyzeItemEntityWithAtlasKernel;
use Illuminate\Http\Request;

class ItemEntityReviewController extends Controller
{
    public function index(Request $request)
    {
        return ItemEntity::query()
            ->where('decision', 'needs_review')
            ->where('is_latest', true)
            ->orderByDesc('confidence')
            ->paginate(20);
    }

    public function approve(int $id)
    {
        $entity = ItemEntity::findOrFail($id);

        $entity->update([
            'decision' => 'auto_accept',
            'extensions' => array_merge(
                $entity->extensions ?? [],
                [
                    'human_review' => [
                        'action' => 'approve',
                        'reviewed_at' => now()->toISOString(),
                        'reviewer_id' => auth()->id(),
                    ],
                ]
            ),
        ]);

        // ★ 人間判断を次世代に反映
        AnalyzeItemEntityWithAtlasKernel::dispatch(
            itemId: $entity->item_id,
            entityType: $entity->entity_type,
            rawValue: $entity->raw_value,
            knownAssetsRef: null,
            reason: 'human_feedback'
        );

        return response()->json(['ok' => true]);
    }

    public function reject(int $id)
    {
        $entity = ItemEntity::findOrFail($id);

        $entity->update([
            'decision' => 'rejected',
            'extensions' => array_merge(
                $entity->extensions ?? [],
                [
                    'human_review' => [
                        'action' => 'reject',
                        'reviewed_at' => now()->toISOString(),
                        'reviewer_id' => auth()->id(),
                    ],
                ]
            ),
        ]);

        AnalyzeItemEntityWithAtlasKernel::dispatch(
            itemId: $entity->item_id,
            entityType: $entity->entity_type,
            rawValue: $entity->raw_value,
            knownAssetsRef: null,
            reason: 'human_feedback'
        );

        return response()->json(['ok' => true]);
    }

    public function reanalyze(Request $request, int $id)
    {
        $entity = ItemEntity::findOrFail($id);

        $reason = $request->input('reason', 'manual_reanalyze');

        AnalyzeItemEntityWithAtlasKernel::dispatch(
            itemId: $entity->item_id,
            entityType: $entity->entity_type,
            rawValue: $entity->raw_value,
            knownAssetsRef: null,
            reason: $reason
        )->onQueue('reanalyze');

        return response()->json(['ok' => true]);
    }
}

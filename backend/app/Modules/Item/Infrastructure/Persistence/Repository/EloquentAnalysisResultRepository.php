<?php
namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use App\Modules\Item\Domain\Dto\AtlasAnalysisResult;
use Illuminate\Support\Facades\DB;

final class EloquentAnalysisResultRepository implements AnalysisResultRepository
{
    public function save(int $itemId, AtlasAnalysisResult $result): void
    {
        DB::table('analysis_results')->insert([
            'item_id'           => $itemId,
            'tags'              => json_encode($result->tags),
            'confidence'        => json_encode($result->confidence),
            'generated_version' => $result->version,
            'raw_text'          => $result->rawText,
            'status'            => 'active',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    public function markRejected(int $itemId): void
    {
        DB::table('analysis_results')
            ->where('item_id', $itemId)
            ->where('status', 'active')
            ->update([
                'status'     => 'rejected',
                'updated_at' => now(),
            ]);
    }
}
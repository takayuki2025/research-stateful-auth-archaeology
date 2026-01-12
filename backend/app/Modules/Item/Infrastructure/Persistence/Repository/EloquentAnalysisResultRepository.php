<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Models\AnalysisResult as EloquentAnalysisResult;
use App\Modules\Item\Domain\Entity\AnalysisResult;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use Illuminate\Support\Facades\DB;

final class EloquentAnalysisResultRepository implements AnalysisResultRepository
{
    public function save(int $itemId, array $payload): void
    {
        $analysis = $payload['analysis'] ?? $payload;

        $canonical = data_get($analysis, 'integration.brand_identity.canonical');
        $confBrand = (float) data_get($analysis, 'integration.brand_identity.confidence', 0.0);

        $condition = data_get($analysis, 'extraction.condition', []);
        $color     = data_get($analysis, 'extraction.color', []);

        $model   = data_get($analysis, 'lineage.model', 'AtlasKernel-unknown');
        $rawText = data_get($payload, 'input.raw_text') ?? data_get($analysis, 'input.raw_text');

        DB::table('analysis_results')->insert([
            // ★ analysis_request_id は “save時に分かる”なら入れる。分からないなら後で別UseCaseで紐付ける。
            // 'analysis_request_id' => $requestId,

            'item_id' => $itemId,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'tags' => json_encode([
                'brand'     => $canonical,
                'condition' => $condition,
                'color'     => $color,
            ], JSON_UNESCAPED_UNICODE),
            'confidence' => json_encode([
                'score' => $confBrand, // ★ UI側が score を読むなら score に統一
            ], JSON_UNESCAPED_UNICODE),
            'generated_version' => $model,
            'raw_text'          => $rawText,
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

    public function markDecided(int $itemId, string $decidedBy, int $decidedUserId): void
    {
        DB::table('analysis_results')
            ->where('item_id', $itemId)
            ->where('status', 'active')
            ->update([
                'status'          => 'decided',
                'decided_by'      => $decidedBy,
                'decided_user_id' => $decidedUserId,
                'decided_at'      => now(),
                'updated_at'      => now(),
            ]);
    }

    public function findByRequestId(int $requestId): ?AnalysisResult
    {
        $row = EloquentAnalysisResult::query()
            ->where('analysis_request_id', $requestId)
            ->where('status', 'active')
            ->latest('created_at')
            ->first();

        return $row ? AnalysisResult::fromEloquent($row) : null;
    }
}
<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use App\Modules\Item\Domain\Entity\AnalysisResult;
use Illuminate\Support\Facades\DB;

final class EloquentAnalysisResultRepository implements AnalysisResultRepository
{
    public function save(int $itemId, array $payload): void
{
    $analysisRequestId = $payload['analysis_request_id']
        ?? throw new \InvalidArgumentException('analysis_request_id is required');

    DB::table('analysis_results')->insert([
        'analysis_request_id' => $analysisRequestId,
        'item_id'             => $itemId,

        'brand_name'          => $payload['brand_name'] ?? null,
        'condition_name'      => $payload['condition_name'] ?? null,
        'color_name'          => $payload['color_name'] ?? null,

        'confidence_map'      => $this->toJsonOrNull($payload['confidence_map'] ?? null),
        'overall_confidence'  => is_numeric($payload['overall_confidence'] ?? null)
                                    ? (float)$payload['overall_confidence']
                                    : null,

        'evidence'            => null,
        'status'              => 'active',
        'created_at'          => now(),
        'updated_at'          => now(),
    ]);
}

    private function toScalarOrNull(mixed $value): ?string
{
    if ($value === null) {
        return null;
    }

    if (is_scalar($value)) {
        return (string) $value;
    }

    if (is_object($value) && method_exists($value, '__toString')) {
        return (string) $value;
    }

    if (is_array($value)) {
        // よくある v3 extraction 構造に対応
        if (isset($value['value']) && is_scalar($value['value'])) {
            return (string) $value['value'];
        }
    }

    return null;
}

private function toJsonOrNull(mixed $value): ?string
{
    if ($value === null) {
        return null;
    }

    if (is_array($value)) {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    if (is_object($value) && method_exists($value, 'toArray')) {
        return json_encode($value->toArray(), JSON_UNESCAPED_UNICODE);
    }

    // 最終防衛
    return json_encode([], JSON_UNESCAPED_UNICODE);
}


    public function supersedeByItem(int $itemId): void
    {
        DB::table('analysis_results')
            ->where('item_id', $itemId)
            ->where('status', 'active')
            ->update([
                'status'     => 'superseded',
                'updated_at'=> now(),
            ]);
    }

    public function findByRequestId(int $analysisRequestId): ?AnalysisResult
{
    $row = DB::table('analysis_results')
        ->where('analysis_request_id', $analysisRequestId)
        ->orderByDesc('id')
        ->first();

    if (! $row) {
        return null;
    }

    return AnalysisResult::reconstruct(
        requestId: (int) $row->analysis_request_id,
        itemId: (int) $row->item_id,
        brandName: $row->brand_name,
        conditionName: $row->condition_name,
        colorName: $row->color_name,
        confidenceMap: json_decode($row->confidence_map, true),
        overallConfidence: (float) $row->overall_confidence,
        evidence: json_decode($row->evidence, true),
        status: $row->status,
        createdAt: new \DateTimeImmutable($row->created_at),
    );
}
}
<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\ValueObject\AnalysisRequestRecord;

final class EloquentAnalysisRequestRepository implements AnalysisRequestRepository
{
    public function create(array $attributes): int
{
    // v3 固定：必須キーをここでも守る（落とすなら早く落とす）
    foreach (['item_id', 'analysis_version', 'raw_text', 'payload_hash', 'idempotency_key'] as $k) {
        if (!array_key_exists($k, $attributes)) {
            throw new \InvalidArgumentException("analysis_request missing key: {$k}");
        }
    }

    return DB::table('analysis_requests')->insertGetId([
        'tenant_id'        => $attributes['tenant_id'] ?? null,
        'item_id'          => $attributes['item_id'],
        'item_draft_id'    => $attributes['item_draft_id'] ?? null,

        'analysis_version'   => $attributes['analysis_version'],
        'requested_version'  => $attributes['requested_version'] ?? null,

        'raw_text'         => $attributes['raw_text'],
        'payload_hash'     => $attributes['payload_hash'],
        'idempotency_key'  => $attributes['idempotency_key'],

        'original_request_id' => $attributes['original_request_id'] ?? null,
        'replay_index'        => $attributes['replay_index'] ?? null,

        'triggered_by_type' => $attributes['triggered_by_type'] ?? 'system',
        'triggered_by'      => $attributes['triggered_by'] ?? null,
        'trigger_reason'    => $attributes['trigger_reason'] ?? null,

        'status'           => 'pending',
        'retry_count'      => 0,

        'created_at' => now(),
        'updated_at' => now(),
    ]);

    }

    public function findOrFail(int $requestId): AnalysisRequestRecord
    {
        $row = DB::table('analysis_requests')
            ->where('id', $requestId)
            ->first();

        if (! $row) {
            throw new ModelNotFoundException(
                "AnalysisRequest not found: id={$requestId}"
            );
        }

        return new AnalysisRequestRecord(
    id: (int) $row->id,
    tenantId: $row->tenant_id,
    itemId: (int) $row->item_id,
    itemDraftId: $row->item_draft_id !== null ? (string) $row->item_draft_id : null,
    analysisVersion: (string) $row->analysis_version,
    rawText: (string) $row->raw_text,
    status: (string) $row->status,
);
    }

    public function markDone(int $requestId): void
    {
        DB::table('analysis_requests')
            ->where('id', $requestId)
            ->update([
                'status'      => 'done',
                'finished_at'=> now(),
                'updated_at' => now(),
            ]);
    }

    public function markFailed(
        int $requestId,
        ?string $errorCode = null,
        ?string $errorMessage = null,
    ): void {
        DB::table('analysis_requests')
            ->where('id', $requestId)
            ->update([
                'status'        => 'failed',
                'error_code'    => $errorCode,
                'error_message' => $errorMessage,
                'finished_at'   => now(),
                'updated_at'    => now(),
            ]);
    }
}

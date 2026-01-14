<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\ValueObject\AnalysisRequestRecord;
use App\Modules\Item\Domain\Entity\AnalysisRequest;
use App\Models\AnalysisRequest as AnalysisRequestModel;
// use App\Models\AnalysisRequest as EloquentAnalysisRequest;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class EloquentAnalysisRequestRepository implements AnalysisRequestRepository
{
    public function reserveOrGet(
    ?int $tenantId,
    int $itemId,
    string $analysisVersion,
    string $payloadHash,
    string $idempotencyKey
): AnalysisRequestRecord {
    $now = now();

    // ★ DB 方言を吸収（MySQL / SQLite 両対応）
    DB::table('analysis_requests')->insertOrIgnore([
        'tenant_id'        => $tenantId,
        'item_id'          => $itemId,
        'analysis_version' => $analysisVersion,
        'payload_hash'     => $payloadHash,
        'idempotency_key'  => $idempotencyKey,
        'status'           => 'pending',
        'retry_count'      => 0,
        'created_at'       => $now,
        'updated_at'       => $now,
    ]);

    $row = DB::table('analysis_requests')
        ->where('idempotency_key', $idempotencyKey)
        ->first();

    if (! $row) {
        throw new \RuntimeException('analysis_requests reserveOrGet failed.');
    }

    $this->appendEvent($row->id, 'reserved', [
        'tenant_id' => $tenantId,
        'item_id' => $itemId,
        'analysis_version' => $analysisVersion,
        'payload_hash' => $payloadHash,
    ]);

    return new AnalysisRequestRecord(
        id: (int)$row->id,
        tenantId: $row->tenant_id !== null ? (int)$row->tenant_id : null,
        itemId: (int)$row->item_id,
        analysisVersion: (string)$row->analysis_version,
        payloadHash: (string)$row->payload_hash,
        idempotencyKey: (string)$row->idempotency_key,
        status: (string)$row->status,
        retryCount: (int)$row->retry_count,
    );
}

    public function markRunning(int $requestId): bool
    {
        $now = CarbonImmutable::now();

        // CAS: pending/failed のみ running に遷移可能
        $affected = DB::table('analysis_requests')
            ->where('id', $requestId)
            ->whereIn('status', ['pending', 'failed'])
            ->update([
                'status' => 'running',
                'started_at' => $now,
                'retry_count' => DB::raw('retry_count + 1'),
                'updated_at' => $now,
            ]);

        if ($affected === 1) {
            $this->appendEvent($requestId, 'started', ['started_at' => (string)$now]);
            return true;
        }

        return false;
    }

    public function markDone(int $requestId): void
    {
        $now = CarbonImmutable::now();

        DB::table('analysis_requests')
            ->where('id', $requestId)
            ->update([
                'status' => 'done',
                'finished_at' => $now,
                'updated_at' => $now,
            ]);

        $this->appendEvent($requestId, 'completed', ['finished_at' => (string)$now]);
    }

    public function markFailed(int $requestId, string $errorCode, string $errorMessage): void
    {
        $now = CarbonImmutable::now();

        DB::table('analysis_requests')
            ->where('id', $requestId)
            ->update([
                'status' => 'failed',
                'error_code' => mb_substr($errorCode, 0, 64),
                'error_message' => mb_substr($errorMessage, 0, 65000),
                'finished_at' => $now,
                'updated_at' => $now,
            ]);

        $this->appendEvent($requestId, 'failed', [
            'error_code' => $errorCode,
            'error_message' => mb_substr($errorMessage, 0, 2000),
            'finished_at' => (string)$now,
        ]);
    }

    public function appendEvent(int $requestId, string $eventType, array $payload = []): void
    {
        DB::table('analysis_request_events')->insert([
            'analysis_request_id' => $requestId,
            'event_type' => $eventType,
            'event_payload' => !empty($payload) ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null,
            'created_at' => CarbonImmutable::now(),
        ]);
    }

    public function listByShopCode(string $shopCode): array
    {
        // review_decisions の最新（max(id)）を request 単位で拾う
        $latestDecisionIdSub = DB::table('review_decisions')
            ->selectRaw('analysis_request_id, MAX(id) as max_id')
            ->groupBy('analysis_request_id');

        return AnalysisRequestModel::query()
            ->select(
                'analysis_requests.id',
                'analysis_requests.item_id',
                'analysis_requests.status',
                'analysis_requests.analysis_version',
                'analysis_requests.created_at',
                'rd.decision_type as decision',
                'rd.decided_at as decided_at'
            )
            ->join('items', 'analysis_requests.item_id', '=', 'items.id')
            ->join('shops', 'items.shop_id', '=', 'shops.id')
            ->where('shops.shop_code', $shopCode)
            ->leftJoinSub($latestDecisionIdSub, 'ld', function ($join) {
                $join->on('ld.analysis_request_id', '=', 'analysis_requests.id');
            })
            ->leftJoin('review_decisions as rd', 'rd.id', '=', 'ld.max_id')
            ->orderByDesc('analysis_requests.created_at')
            ->get()
            ->map(fn ($m) => [
                'id'               => (int)$m->id,
                'item_id'          => (int)$m->item_id,
                'status'           => (string)$m->status,
                'analysis_version' => (string)$m->analysis_version,
                'created_at'       => (string)$m->created_at,
                'decision'         => $m->decision ? (string)$m->decision : null,
                'decided_at'       => $m->decided_at ? (string)$m->decided_at : null,
            ])
            ->toArray();
    }



    public function findOrFail(int $id): AnalysisRequest
    {
        $model = AnalysisRequestModel::findOrFail($id);

        return AnalysisRequest::fromEloquent($model);
    }

    public function getById(int $id): AnalysisRequest
    {
        $model = AnalysisRequestModel::query()->whereKey($id)->firstOrFail();
        return AnalysisRequest::fromEloquent($model);
    }

    public function countReplaysInPeriod(
        int $originalRequestId,
        CarbonImmutable $from,
        CarbonImmutable $to,
    ): int {
        return AnalysisRequestModel::query()
            ->where('original_request_id', $originalRequestId)
            ->whereBetween('created_at', [$from->toDateTimeString(), $to->toDateTimeString()])
            ->count();
    }

    public function save(AnalysisRequest $request): int
    {
        $now = CarbonImmutable::now();

        $id = DB::table('analysis_requests')->insertGetId([
            'tenant_id'           => $request->tenantId,
            'item_id'             => $request->itemId,
            'analysis_version'    => $request->analysisVersion,
            'requested_version'   => $request->requestedVersion,
            'payload_hash'        => $request->payloadHash,
            'idempotency_key'     => $request->idempotencyKey,
            'status'              => $request->status,
            'started_at'          => $request->startedAt?->format('Y-m-d H:i:s'),
            'finished_at'         => $request->finishedAt?->format('Y-m-d H:i:s'),
            'original_request_id' => $request->originalRequestId,
            'retry_count'         => $request->retryCount,
            'triggered_by_type'   => $request->triggeredByType,
            'triggered_by'        => $request->triggeredBy,
            'trigger_reason'      => $request->triggerReason,
            'replay_index'        => $request->replayIndex,
            'created_at'          => $now->toDateTimeString(),
            'updated_at'          => $now->toDateTimeString(),
        ]);

        // 任意：event ledger
        DB::table('analysis_request_events')->insert([
            'analysis_request_id' => $id,
            'event_type'          => 'created',
            'event_payload'       => json_encode([
                'type'    => $request->originalRequestId ? 'replay' : 'initial',
                'version' => $request->analysisVersion,
            ], JSON_UNESCAPED_UNICODE),
            'created_at'          => $now->toDateTimeString(),
        ]);

        return (int)$id;
    }

        public function getStatus(int $requestId): string
{
    $status = DB::table('analysis_requests')
        ->where('id', $requestId)
        ->value('status');

    if ($status === null) {
        throw new \RuntimeException("analysis_request not found: {$requestId}");
    }

    return (string) $status;
}
}
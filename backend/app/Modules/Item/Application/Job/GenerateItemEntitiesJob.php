<?php

namespace App\Modules\Item\Application\Job;

use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\Item\Domain\Service\AtlasKernelService;
use App\Modules\Item\Domain\Repository\AnalysisResultRepository;
use App\Modules\Item\Application\UseCase\ApplyProvisionalAnalysisUseCase;
use App\Modules\Item\Application\Support\AtlasIdempotency;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Modules\Item\Domain\Service\AtlasKernelPort;

final class GenerateItemEntitiesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // DBキューでも十分。タイムアウトはAtlas呼び出しが長いなら調整
    public int $timeout = 120;
    public int $tries = 5;
    public int $backoff = 10;

    public function __construct(
        private int $itemId,
        private string $rawText,
        private ?int $tenantId
    ) {}

    public function handle(
    AtlasKernelPort $atlasKernel,
    AnalysisResultRepository $analysisRepo,
    ApplyProvisionalAnalysisUseCase $applyProvisional,
    AnalysisRequestRepository $requests,
): void {
        $analysisVersion = config('atlaskernel.analysis_version', 'v3.0.0');

        // 1) 正規化 → payload_hash / idempotency_key 作成
        $normalized = AtlasIdempotency::normalizeRawText($this->rawText);
        $payloadHash = AtlasIdempotency::payloadHash([
            'tenant_id' => $this->tenantId,
            'item_id' => $this->itemId,
            'raw_text' => $normalized,
            'analysis_version' => $analysisVersion,
        ]);

        $idempotencyKey = AtlasIdempotency::idempotencyKey(
            tenantId: $this->tenantId,
            itemId: $this->itemId,
            analysisVersion: $analysisVersion,
            payloadHash: $payloadHash
        );

        // 2) reserve（存在するなら取り出す。doneなら即終了）
        $req = $requests->reserveOrGet(
            tenantId: $this->tenantId,
            itemId: $this->itemId,
            analysisVersion: $analysisVersion,
            payloadHash: $payloadHash,
            idempotencyKey: $idempotencyKey
        );

        if ($req->isDone()) {
            return;
        }

        // 3) CASでrunning取得できなければ終了（誰かが処理中/完了）
        if (!$requests->markRunning($req->id)) {
            return;
        }

        try {
            // 4) Atlas解析
            $result = $atlasKernel->requestAnalysis(
                itemId: $this->itemId,
                rawText: $this->rawText,
                tenantId: $this->tenantId,
            );

            // 5) analysis_results 保存（既存）
            $analysisRepo->save($this->itemId, [
                'analysis' => $result->toArray(),
                'status'   => 'provisional',
                'analysis_version' => $analysisVersion,
                'payload_hash' => $payloadHash,
                'idempotency_key' => $idempotencyKey,
            ]);

            // 6) 仮Entity反映（既存）
            $applyProvisional->handle(
                $this->itemId,
                $result->toArray()
            );

            // 7) done
            $requests->markDone($req->id);

        } catch (\Throwable $e) {
            $requests->markFailed(
                $req->id,
                errorCode: (string)class_basename($e),
                errorMessage: $e->getMessage()
            );

            // Laravelのretryに委譲
            throw $e;
        }
    }


}
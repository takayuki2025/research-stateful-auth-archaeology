<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class AnalyzeEntityWithAtlasKernel implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** 再試行回数 */
    public int $tries = 3;

    /** Job 全体のタイムアウト（秒） */
    public int $timeout = 30;

    /**
     * @param array{
     *   item_id:int,
     *   raw_value:string,
     *   entity_type?:string
     * } $payload
     */
    public function __construct(
        private readonly array $payload
    ) {
    }

    public function handle(): void
    {
        $endpoint = config('atlaskernel.endpoint');

        Log::info('[AtlasKernel] request', [
            'endpoint' => $endpoint,
            'payload'  => $this->payload,
        ]);

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->post($endpoint, $this->payload);
        } catch (Throwable $e) {
            Log::error('[AtlasKernel] HTTP exception', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }

        if (! $response->successful()) {
            Log::error('[AtlasKernel] HTTP failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('AtlasKernel HTTP error');
        }

        $json = $response->json();

        if (! is_array($json) || ! isset($json['results'])) {
            Log::error('[AtlasKernel] invalid response', [
                'response' => $json,
            ]);
            throw new \RuntimeException('Invalid AtlasKernel response');
        }

        Log::info('[AtlasKernel] response summary', [
            'engine'  => $json['engine'] ?? null,
            'count'   => count($json['results']),
        ]);

        $this->persistResults(
            itemId: (int) $this->payload['item_id'],
            results: $json['results']
        );
    }

    /**
     * AtlasKernel の結果を DB に反映する
     *
     * @param int   $itemId
     * @param array<int,array<string,mixed>> $results
     */
    private function persistResults(int $itemId, array $results): void
    {
        foreach ($results as $result) {
            $decision = $result['decision'] ?? null;
            $entity   = $result['entity_type'] ?? null;

            match ($decision) {
                'auto_accept'   => $this->handleAutoAccept($itemId, $result),
                'needs_review',
                'rejected'      => $this->handleHumanReview($itemId, $result),
                default         => $this->handleUnknownDecision($itemId, $result),
            };
        }
    }

    /**
     * 自動採用（基本ルート）
     */
    private function handleAutoAccept(int $itemId, array $result): void
    {
        Log::info('[AtlasKernel] auto_accept', [
            'item_id'     => $itemId,
            'entity_type' => $result['entity_type'] ?? null,
            'value'       => $result['canonical_value'] ?? null,
            'confidence'  => $result['confidence'] ?? null,
        ]);

        // ここでは「DB反映専用の Service」に渡すのが理想
        // 例:
        // app(ItemEntityApplyService::class)->apply($itemId, $result);
    }

    /**
     * 人手レビュー行き（品質向上ルート）
     */
    private function handleHumanReview(int $itemId, array $result): void
    {
        Log::info('[AtlasKernel] human_review', [
            'item_id'     => $itemId,
            'entity_type' => $result['entity_type'] ?? null,
            'raw'         => $result['raw_value'] ?? null,
            'candidates'  => $result['candidates'] ?? [],
        ]);

        // review_queue / entity_review_requests 等に保存
        // 後で人手で「正解」を選ばせ、辞書 or policy に還元
    }

    /**
     * 想定外 decision（ログのみ）
     */
    private function handleUnknownDecision(int $itemId, array $result): void
    {
        Log::warning('[AtlasKernel] unknown decision', [
            'item_id' => $itemId,
            'result'  => $result,
        ]);
    }
}

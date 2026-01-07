<?php

namespace App\Jobs;

use App\Models\ItemEntity;
use App\Models\ItemEntityAudit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class AnalyzeItemEntityWithAtlasKernel implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $itemId,
        public string $entityType,
        public string $rawValue,
        public ?string $knownAssetsRef = null,
        public string $reason = 'manual_reanalyze',
        public array $context = []
    ) {
    }

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [10, 30, 60];

    public function handle(): void
    {
        // ① FastAPI へ送る payload
        $input = [
            'entity_type' => $this->entityType,
            'raw_value' => $this->rawValue,
            'known_assets_ref' => $this->knownAssetsRef,
        ];

        // ② HTTP 呼び出し（docker / process 完全排除）
        $response = Http::timeout(30)->post(
            'http://python_atlaskernel:8000/analyze',
            $input
        );

        if (!$response->successful()) {
            throw new \RuntimeException(
                'AtlasKernel HTTP failed: ' . $response->body()
            );
        }

        $payload = $response->json();

        if (!is_array($payload)) {
            throw new \RuntimeException('AtlasKernel response is not JSON');
        }

        // ③ 過去をすべて latest=false
        ItemEntity::where('item_id', $this->itemId)
            ->where('entity_type', $this->entityType)
            ->update(['is_latest' => false]);

        // ④ extensions に reanalyze 情報を付加
        $extensions = $payload['extensions'] ?? [];
        $extensions['reanalyze'] = [
            'reason' => $this->reason,
            'at' => now()->toISOString(),
        ];

        // ⑤ 最新として保存
        $entity = ItemEntity::create([
            'item_id' => $this->itemId,
            'entity_type' => $payload['entity_type'],
            'raw_value' => $payload['raw_value'],
            'canonical_value' => $payload['canonical_value'],
            'confidence' => (float)$payload['confidence'],
            'decision' => $payload['decision'],
            'policy_version' => data_get($payload, 'extensions.policy_trace.policy_schema'),
            'schema_version' => $payload['schema_version'],
            'engine_version' => $payload['engine_version'],
            'extensions' => $extensions,
            'is_latest' => true,
        ]);

        // ⑥ 監査ログ（不変）
        ItemEntityAudit::create([
            'item_entity_id' => $entity->id,
            'decision' => $entity->decision,
            'confidence' => $entity->confidence,
            'payload' => $payload,
            'extensions' => $extensions,
        ]);
    }
}

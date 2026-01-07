<?php

namespace App\Jobs;

use App\Models\ItemEntity;
use App\Models\ItemEntityAudit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;

class ReanalyzeItemEntity implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $itemEntityId,
        public string $reason = 'manual_reprocess'
    ) {
    }

    public function handle(): void
    {
        $old = ItemEntity::findOrFail($this->itemEntityId);

        // 旧エンティティを非最新化
        $old->update(['is_latest' => false]);

        $input = [
            'entity_type' => $old->entity_type,
            'raw_value' => $old->raw_value,
        ];

        $run = Process::run(
            ['atlaskernel'],
            input: json_encode($input, JSON_UNESCAPED_UNICODE) . "\n"
        );

        if (!$run->successful()) {
            throw new \RuntimeException($run->errorOutput());
        }

        $payload = json_decode(trim($run->output()), true);

        $new = ItemEntity::create([
            'item_id' => $old->item_id,
            'entity_type' => $payload['entity_type'],
            'raw_value' => $payload['raw_value'],
            'canonical_value' => $payload['canonical_value'],
            'confidence' => $payload['confidence'],
            'decision' => $payload['decision'],
            'policy_version' => data_get($payload, 'extensions.policy_trace.policy_schema'),
            'schema_version' => $payload['schema_version'],
            'engine_version' => $payload['engine_version'],
            'extensions' => $payload['extensions'] ?? null,
            'is_latest' => true,
        ]);

        ItemEntityAudit::create([
            'item_entity_id' => $new->id,
            'decision' => $new->decision,
            'confidence' => $new->confidence,
            'payload' => array_merge(
                $payload,
                ['reprocess_reason' => $this->reason]
            ),
        ]);
    }
}

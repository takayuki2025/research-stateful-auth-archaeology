<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\ItemEntity;


class ExportEntityTrainingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-entity-training-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export human-reviewed entity data for AtlasKernel training';

    /**
     * Execute the console command.
     */
    public function handle()
{
    $rows = \App\Models\ItemEntity::query()
        ->where('is_latest', true)
        ->whereNotNull('extensions->human_review') // 人間の確定判断
        ->get();

    $path = storage_path('app/atlaskernel/training_entity_labels.jsonl');
    @mkdir(dirname($path), 0777, true);

    $fp = fopen($path, 'w');
    foreach ($rows as $r) {
        $label = data_get($r->extensions, 'human_review.action'); // approve/reject
        fwrite($fp, json_encode([
            'entity_type' => $r->entity_type,
            'raw_value' => $r->raw_value,
            'canonical_value' => $r->canonical_value,
            'label' => $label,
            'confidence' => $r->confidence,
            'engine_version' => $r->engine_version,
        ], JSON_UNESCAPED_UNICODE) . "\n");
    }
    fclose($fp);

    $this->info("Exported: {$path}");
}
}
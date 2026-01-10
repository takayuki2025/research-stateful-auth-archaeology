<?php

namespace App\Modules\Item\Application\Command;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class ImportLegacyItemsCommand extends Command
{
    protected $signature = 'legacy:import-items
        {path : Path to JSON file}
        {--source=legacy-db : Source system label}
        {--dry-run : Validate only, do not insert}';

    protected $description = 'Import legacy items into legacy_items table (no AI processing)';

    public function handle(): int
    {
        $path = $this->argument('path');
        $source = $this->option('source');
        $dryRun = $this->option('dry-run');

        if (!File::exists($path)) {
            $this->error("File not found: {$path}");
            return Command::FAILURE;
        }

        $json = File::get($path);
        $records = json_decode($json, true);

        if (!is_array($records)) {
            $this->error('Invalid JSON format');
            return Command::FAILURE;
        }

        $this->info('Importing legacy items...');
        $this->info('Source: ' . $source);
        $this->info('Records: ' . count($records));
        if ($dryRun) {
            $this->warn('Dry-run mode enabled (no insert)');
        }

        DB::beginTransaction();

        try {
            foreach ($records as $index => $row) {

                $payload = [
                    'source_system'    => $source,
                    'source_record_id'=> $row['source_record_id'] ?? (string) Str::uuid(),

                    'raw_name'        => $row['raw_name'] ?? null,
                    'raw_description' => $row['raw_description'] ?? null,
                    'raw_brand'       => $row['raw_brand'] ?? null,
                    'raw_category'    => $row['raw_category'] ?? null,
                    'raw_condition'   => $row['raw_condition'] ?? null,

                    'raw_attributes'  => isset($row['raw_attributes'])
                        ? json_encode($row['raw_attributes'], JSON_UNESCAPED_UNICODE)
                        : null,

                    'raw_price'       => $row['raw_price'] ?? null,
                    'raw_currency'    => $row['raw_currency'] ?? null,

                    'raw_images'      => isset($row['raw_images'])
                        ? json_encode($row['raw_images'], JSON_UNESCAPED_UNICODE)
                        : null,

                    'replay_status'   => 'pending',
                    'replayed_at'     => null,

                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];

                if ($dryRun) {
                    $this->line("[DRY] {$index}: " . ($payload['raw_name'] ?? 'no-name'));
                    continue;
                }

                DB::table('legacy_items')->insert($payload);
            }

            if ($dryRun) {
                DB::rollBack();
                $this->info('Dry-run completed. No data inserted.');
            } else {
                DB::commit();
                $this->info('Legacy items imported successfully.');
            }

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Import failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
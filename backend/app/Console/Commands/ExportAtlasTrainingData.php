<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class ExportAtlasTrainingData extends Command
{
    protected $signature = 'atlas:export-training-data';
    protected $description = 'Export Atlas review decisions as training data';

    public function handle(): int
    {
        $rows = DB::table('review_decisions')
            ->whereIn('decision_type', ['approve', 'edit_confirm', 'manual_override'])
            ->get();

        foreach ($rows as $row) {
            $this->exportRow($row);
        }

        $this->info('Atlas training data exported.');
        return self::SUCCESS;
    }

    private function exportRow(object $row): void
    {
        $data = [
            'analysis_request_id' => $row->analysis_request_id,
            'before' => json_decode($row->before_snapshot, true),
            'after'  => json_decode($row->after_snapshot, true),
            'decision_type' => $row->decision_type,
            'decided_at' => $row->decided_at,
        ];

        file_put_contents(
            storage_path('atlas/training.jsonl'),
            json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND
        );
    }
}
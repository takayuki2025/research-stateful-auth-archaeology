<?php

namespace App\Modules\Item\Infrastructure\Persistence\Repository;

use Illuminate\Support\Facades\DB;

final class EloquentAnalysisRequestEventRepository
{
    public function record(
        int $requestId,
        string $eventType,
        array $payload = []
    ): void {
        DB::table('analysis_request_events')->insert([
            'analysis_request_id' => $requestId,
            'event_type'          => $eventType,
            'event_payload'       => json_encode($payload),
            'created_at'          => now(),
        ]);
    }
}
<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Admin\TrustLedger;

use Illuminate\Http\JsonResponse;

final class ReplayWebhookEventController
{
    public function __invoke(string $eventId): JsonResponse
    {
        // TODO: 次フェーズで UseCase 接続（実リプレイ）
        return response()->json([
            'ok' => false,
            'message' => 'Replay not implemented yet.',
            'event_id' => $eventId,
        ], 501);
    }
}
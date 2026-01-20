<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Admin\TrustLedger;

use App\Modules\Payment\Domain\Repository\PaymentQueryRepository;
use Illuminate\Http\JsonResponse;

final class GetWebhookEventDetailController
{
    public function __construct(
        private PaymentQueryRepository $webhookEvents,
    ) {
    }

    public function __invoke(string $eventId): JsonResponse
    {
        $row = $this->webhookEvents->findWebhookEventByEventId($eventId);

        if (!$row) {
            return response()->json([
                'error_type' => 'NotFound',
                'message' => 'Webhook event not found.',
                'event_id' => $eventId,
            ], 404);
        }

        return response()->json($row);
    }
}
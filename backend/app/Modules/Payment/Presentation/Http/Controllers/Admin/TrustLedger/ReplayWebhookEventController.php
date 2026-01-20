<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Admin\TrustLedger;

use App\Modules\Payment\Application\UseCase\Admin\TrustLedger\ReplayWebhookEventUseCase;
use Illuminate\Http\JsonResponse;

final class ReplayWebhookEventController
{
    public function __construct(
        private ReplayWebhookEventUseCase $useCase
    ) {}

    public function __invoke(string $eventId): JsonResponse
{
    $result = $this->useCase->handle($eventId);

    return response()->json([
        'ok' => true,
        'result' => $result,
    ]);
}
}
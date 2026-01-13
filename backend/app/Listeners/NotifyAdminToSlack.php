<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Support\Facades\Http;

final class NotifyAdminToSlack
{
    public function handle($event): void
    {
        $webhook = config('services.slack.audit_webhook');

        if (!$webhook) {
            return;
        }

        Http::post($webhook, [
            'text' => sprintf(
                "[AtlasKernel]\nDecision: %s\nRequest: #%d\nActor: %s (%d)\nNote: %s",
                class_basename($event),
                $event->analysisRequestId,
                $event->actorRole,
                $event->actorUserId,
                $event->note ?? '-'
            ),
        ]);
    }
}
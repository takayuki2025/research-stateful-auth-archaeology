<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Modules\Item\Domain\Event\Atlas\AtlasManualOverrideOccurred;
use Illuminate\Support\Facades\Log;

final class NotifyAdminOnManualOverride
{
    public function handle(AtlasManualOverrideOccurred $event): void
    {
        // 今は Log。後で Slack / Mail に差し替え
        Log::channel('audit')->warning('Atlas manual override occurred', [
            'analysis_request_id' => $event->analysisRequestId,
            'actor_user_id'       => $event->actorUserId,
            'actor_role'          => $event->actorRole,
            'after_snapshot'      => $event->afterSnapshot,
            'note'                => $event->note,
        ]);
    }
}
<?php

namespace App\Modules\Item\Event;

final class ZZZItemPublishedEvent
{
    public function __construct(
        public readonly int $itemId,
        public readonly string $rawText,
        public readonly ?int $tenantId,
    ) {
    }
}

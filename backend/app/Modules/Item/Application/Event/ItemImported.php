<?php

namespace App\Modules\Item\Application\Event;

final class ItemImported
{
    public function __construct(
        public readonly int $itemId,
        public readonly string $rawText,
        public readonly ?int $tenantId,
        public readonly string $source, // publish | legacy | replay
        public readonly ?string $itemDraftId = null,
    ) {}
}
<?php

namespace App\Modules\Item\Domain\Event;

final class ItemPublished
{
    public function __construct(
        public readonly int $itemId,
        public readonly string $rawText,
        public readonly ?int $tenantId,
    ) {
    }
}

<?php

namespace App\Modules\Payment\Application\Dto\Admin\TrustLedger;

final class CursorPageDto
{
    /** @param array<int, array<string,mixed>> $items */
    public function __construct(
        public readonly array $items,
        public readonly ?string $next_cursor,
    ) {
    }

    public function toArray(): array
    {
        return [
            'items' => $this->items,
            'next_cursor' => $this->next_cursor,
        ];
    }
}
<?php

namespace App\Modules\Item\Application\Dto\Item;

final class PublishItemOutput
{
    public function __construct(
        public readonly string $itemId,
        public readonly string $status,
    ) {}
}
<?php

namespace App\Modules\Item\Application\Dto\Item;

final class CreateItemDraftOutput
{
    public function __construct(
        public readonly string $draftId,
        public readonly string $status,
        public readonly bool $editable,
    ) {}
}
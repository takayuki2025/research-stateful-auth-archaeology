<?php

namespace App\Modules\Item\Application\Dto\Item;

final class PublishItemDraftInput
{
    public function __construct(
        public readonly string $draftId,
        public readonly string $itemOrigin, // USER_PERSONAL | SHOP_MANAGED
        public readonly ?int $shopId,
    ) {
    }
}

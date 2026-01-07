<?php

namespace App\Modules\Item\Domain\Repository;

use App\Modules\Item\Domain\Entity\ItemDraft;
use App\Modules\Item\Domain\ValueObject\ItemDraftId;

interface ItemDraftRepository
{
    public function nextIdentity(): ItemDraftId;

    public function save(ItemDraft $draft): void;

    public function findById(string $draftId): ?ItemDraft;

    public function delete(string $draftId): void;
}
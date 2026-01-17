<?php

namespace App\Modules\Item\Domain\Repository;

interface ItemEntityRepository
{
    public function markAllAsNotLatest(int $itemId): void;

    public function create(array $attrs): int;

    public function existsByDecisionId(int $reviewDecisionId): bool;

}
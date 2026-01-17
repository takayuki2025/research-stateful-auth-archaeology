<?php

namespace App\Modules\Item\Domain\Repository;

interface ConditionEntityQueryRepository
{
     public function resolveCanonicalByEntityId(int $entityId): int;

    public function resolveCanonicalByName(string $input): ?int;

    /**
     * Edit Confirm 用
     * canonical brand 一覧
     *
     * @return array<int, array{id:int, canonical_name:string}>
     */
    public function listCanonicalOptions(): array;
}
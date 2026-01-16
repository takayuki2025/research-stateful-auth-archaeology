<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Repository;

interface ConditionEntityRepository
{
    public function resolveOrCreate(string $input): int;
}
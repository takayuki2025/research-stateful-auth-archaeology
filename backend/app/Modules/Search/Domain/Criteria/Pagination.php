<?php

namespace App\Modules\Search\Domain\Criteria;

final class Pagination
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage
    ) {
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }
}

<?php

namespace App\Modules\Search\Domain\Criteria;

final class ItemSearchCriteria
{
    public function __construct(
        public readonly ?string $keyword = null,
        public readonly ?int $shopId = null,
        public readonly bool $onlyPublished = true,
        public readonly ?SortOption $sort = null,
        public readonly ?Pagination $pagination = null,
    ) {
    }
}

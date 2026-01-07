<?php

namespace App\Modules\Search\Application\UseCase\Query;

use App\Modules\Search\Domain\Criteria\ItemSearchCriteria;
use App\Modules\Search\Domain\Repository\ItemSearchRepository;
use App\Modules\Search\Domain\Collection\SearchResultItems;

final class SearchPublicItemsUseCase
{
    public function __construct(
        private ItemSearchRepository $items
    ) {
    }

    public function handle(ItemSearchCriteria $criteria): SearchResultItems
    {
        return $this->items->search($criteria);
    }
}

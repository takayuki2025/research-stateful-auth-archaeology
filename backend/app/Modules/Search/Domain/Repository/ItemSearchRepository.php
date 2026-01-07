<?php

namespace App\Modules\Search\Domain\Repository;

use App\Modules\Search\Domain\Criteria\ItemSearchCriteria;
use App\Modules\Search\Domain\Collection\SearchResultItems;

interface ItemSearchRepository
{
    public function search(ItemSearchCriteria $criteria): SearchResultItems;
}

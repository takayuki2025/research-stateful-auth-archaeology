<?php

namespace App\Modules\Item\Application\Query;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PublicCatalogQueryService
{
    /**
     * @return LengthAwarePaginator<array>
     */
    public function paginate(
        int $limit,
        int $page,
        ?string $keyword,
        array $excludeShopIds = [],
    ): LengthAwarePaginator;
}

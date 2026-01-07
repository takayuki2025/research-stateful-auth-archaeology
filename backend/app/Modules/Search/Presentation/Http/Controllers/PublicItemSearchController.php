<?php

namespace App\Modules\Search\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Search\Application\UseCase\Query\SearchPublicItemsUseCase;
use App\Modules\Search\Domain\Criteria\ItemSearchCriteria;
use App\Modules\Search\Domain\Criteria\Pagination;
use App\Modules\Search\Domain\Criteria\SortOption;
use App\Modules\Search\Presentation\Http\Resources\SearchItemResource;
use Illuminate\Http\Request;

final class PublicItemSearchController extends Controller
{
    public function __construct(
        private SearchPublicItemsUseCase $useCase
    ) {
    }

    /**
     * GET /api/public/items/search?q=...&page=1&per_page=20&sort=newest|price_asc|price_desc
     */
    public function __invoke(Request $request)
    {
        $keyword = $request->query('q');

        $page = max(1, (int) $request->query('page', 1));
        $perPage = (int) $request->query('per_page', 20);
        $perPage = ($perPage <= 0) ? 20 : min($perPage, 50);

        $sortKey = (string) $request->query('sort', 'newest');
        $sort = match ($sortKey) {
            'price_asc'  => SortOption::priceAsc(),
            'price_desc' => SortOption::priceDesc(),
            default      => SortOption::newest(),
        };

        $criteria = new ItemSearchCriteria(
            keyword: is_string($keyword) ? $keyword : null,
            shopId: null,
            onlyPublished: true,
            sort: $sort,
            pagination: new Pagination(page: $page, perPage: $perPage)
        );

        $result = $this->useCase->handle($criteria);

        return response()->json([
            'items' => SearchItemResource::collection($result->items()),
            'meta' => [
                'total'    => $result->total(),
                'page'     => $page,
                'per_page' => $perPage,
                'q'        => $criteria->keyword,
                'sort'     => $sortKey,
            ],
        ]);
    }
}
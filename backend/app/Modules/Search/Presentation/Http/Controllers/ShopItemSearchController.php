<?php

namespace App\Modules\Search\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Search\Application\UseCase\Query\SearchShopItemsUseCase;
use App\Modules\Search\Domain\Criteria\ItemSearchCriteria;
use App\Modules\Search\Domain\Criteria\Pagination;
use App\Modules\Search\Domain\Criteria\SortOption;
use App\Modules\Search\Presentation\Http\Resources\SearchItemResource;
use Illuminate\Http\Request;

final class ShopItemSearchController extends Controller
{
    public function __construct(
        private SearchShopItemsUseCase $useCase
    ) {
    }

    /**
     * GET /api/shops/{shopId}/items/search?q=...&page=1&per_page=20&sort=...
     *
     * 注：shopId の取り方はプロジェクト差が出やすいので、
     * - {shopId} ルートパラメータ
     * - shop_id クエリ
     * のどちらでも拾えるようにしています。
     */
    public function __invoke(Request $request, $shopId = null)
    {
        $routeShopId = $shopId ?? $request->route('shopId') ?? $request->route('shop_id');
        $queryShopId = $request->query('shop_id');
        $resolvedShopId = $routeShopId ?? $queryShopId;

        $resolvedShopId = (int) $resolvedShopId;
        if ($resolvedShopId <= 0) {
            return response()->json([
                'message' => 'shop_id is required.',
            ], 422);
        }

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

        // Shop検索は「運営側」前提なら onlyPublished を false にする等、
        // ポリシーに応じて調整してください。ここではまず true に固定。
        $criteria = new ItemSearchCriteria(
            keyword: is_string($keyword) ? $keyword : null,
            shopId: $resolvedShopId,
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
                'shop_id'  => $resolvedShopId,
            ],
        ]);
    }
}
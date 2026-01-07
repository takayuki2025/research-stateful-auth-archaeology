<?php

namespace App\Modules\Item\Application\UseCase\Catalog\Query;

use App\Modules\Item\Infrastructure\Persistence\Query\PublicCatalogItemReadRepository;
use App\Modules\Item\ReadModel\PublicCatalog\PublicCatalogItemCollection;
use App\Modules\Item\ReadModel\PublicCatalog\PublicCatalogItemDto;

final class ListPublicCatalogItemsUseCase
{
    public function __construct(
        private readonly PublicCatalogItemReadRepository $readRepository
    ) {
    }

    public function execute(
        int $limit,
        int $page,
        ?string $keyword,
        array $viewerShopIds,
        ?int $viewerUserId
    ): PublicCatalogItemCollection {

        \Log::info('[PublicCatalog][UseCase entered]', [
            'limit' => $limit,
            'page' => $page,
            'keyword' => $keyword,
            'viewerShopIds' => $viewerShopIds,
            'viewerUserId' => $viewerUserId,
        ]);


        $isShopMember = !empty($viewerShopIds);

        // ★ Repository は「生データ取得だけ」
        $rows = $this->readRepository->paginate(
            limit: $limit,
            page: $page,
            keyword: $keyword
        );

        $items = [];


        foreach ($rows as $row) {

    // ★ 0) item_origin がない場合の保険（運用中の移行期）
    $itemOrigin = $row->item_origin ?? null;

    // ★ 1) ショップ公式出品はトップページから除外
    if ($itemOrigin === 'shop_managed') {
        continue;
    }

    // ★ 2) 表示タイプ判定（個人出品だけ）
    $displayType = null;

    if ($itemOrigin === 'user_personal') {
        // 「一般か、ショップ関係か」は viewerShopIds（ロール等）で判定
        $displayType = $isShopMember ? 'STAR' : 'COMET';
    }

    $items[] = new PublicCatalogItemDto(
        id: (int) $row->id,
        name: (string) $row->name,
        price: (int) $row->price,
        brandPrimary: $row->brand_primary ?? null,
        conditionName: $row->condition_name ?? null,
        colorName: $row->color_name ?? null,
        itemImagePath: $row->item_image ?? null,
        publishedAt: $row->created_at,
        itemOrigin: $itemOrigin,
        displayType: $displayType
    );
}
}



        return new PublicCatalogItemCollection($items);
    }
} 

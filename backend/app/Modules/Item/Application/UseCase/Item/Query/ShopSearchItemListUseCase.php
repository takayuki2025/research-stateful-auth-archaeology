<?php

namespace App\Modules\Item\Application\UseCase\Item\Query;

use App\Modules\Item\Domain\Repository\ItemRepository;
use App\Modules\Item\Application\Dto\Item\ListItemsInputDto;
use App\Modules\Item\Application\Dto\Item\ListItemsOutputDto;
use App\Modules\Item\Application\Assembler\PublicItemAssembler;

final class ShopSearchItemListUseCase
{
    public function __construct(
        private ItemRepository $itemRepository,
    ) {
    }

    public function execute(ListItemsInputDto $input): ListItemsOutputDto
    {
        $shopCode = $input->viewerShopIds[0];

        $paginator = $this->itemRepository->searchByShopCode(
            shopCode: $shopCode,
            keyword: $input->keyword,
            limit: $input->limit,
            page: $input->page,
        );

        $items = collect($paginator->items())
            ->map(
                fn ($model) =>
                PublicItemAssembler::fromEloquent(
                    model: $model,
                    viewerUserId: null,
                    viewerShopIds: [],
                    isFavorited: false,
                    favoritesCount: 0,
                )
            )
            ->all();

        return new ListItemsOutputDto(
            items: $items,
            currentPage: $paginator->currentPage(),
            total: $paginator->total(),
            hasNext: $paginator->hasMorePages(),
        );
    }
}

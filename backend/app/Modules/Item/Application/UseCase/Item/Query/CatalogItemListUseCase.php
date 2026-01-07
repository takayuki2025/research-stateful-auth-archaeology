<?php

namespace App\Modules\Item\Application\UseCase\Item\Query;

use App\Modules\Item\Application\Query\PublicCatalogQueryService;
use App\Modules\Reaction\Domain\Repository\FavoriteRepository;
use App\Modules\Item\Application\Dto\Item\ListItemsInputDto;
use App\Modules\Item\Application\Dto\Item\ListItemsOutputDto;
use App\Modules\Item\Application\Assembler\PublicItemAssembler;
use App\Modules\Reaction\Domain\ValueObject\ReactorId;
use App\Modules\Reaction\Domain\ValueObject\FavoriteTargetId;

final class CatalogItemListUseCase
{
    public function __construct(
        private PublicCatalogQueryService $catalogQuery,
        private FavoriteRepository $favoriteRepository,
    ) {
    }

    public function execute(ListItemsInputDto $input): ListItemsOutputDto
    {
        $paginator = $this->catalogQuery->paginate(
            limit: $input->limit,
            page: $input->page,
            keyword: null,
            excludeShopIds: $input->viewerShopIds,
        );

        $items = collect($paginator->items())
            ->map(function (array $row) use ($input) {

                $itemId = (int) ($row['id'] ?? 0);

                $favoritesCount = $this->favoriteRepository->countByTarget(
                    new FavoriteTargetId($itemId)
                );

                $isFavorited = $input->viewerUserId
                    ? $this->favoriteRepository->exists(
                        new ReactorId($input->viewerUserId),
                        new FavoriteTargetId($itemId)
                    )
                    : false;

                return PublicItemAssembler::fromReadModel(
                    row: $row,
                    viewerUserId: $input->viewerUserId,
                    viewerShopIds: $input->viewerShopIds,
                    isFavorited: $isFavorited,
                    favoritesCount: $favoritesCount,
                );
            })
            ->filter()   // ★ null を除外（超重要）
            ->values();

        return new ListItemsOutputDto(
            items: $items->all(),
            currentPage: $paginator->currentPage(),
            total: $paginator->total(),
            hasNext: $paginator->hasMorePages(),
        );
    }
}

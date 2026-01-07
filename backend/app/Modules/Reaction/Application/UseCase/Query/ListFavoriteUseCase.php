<?php


namespace App\Modules\Reaction\Application\UseCase\Query;


use App\Modules\Reaction\Domain\Repository\FavoriteRepository;
use App\Modules\Item\Application\Assembler\PublicItemAssembler;
use App\Modules\Item\Application\Port\FavoriteItemReadPort;
use App\Modules\Reaction\Domain\ValueObject\FavoriteTargetId;

final class ListFavoriteUseCase
{
    public function __construct(
        private FavoriteRepository $favoriteRepository,
        private FavoriteItemReadPort $favoriteItems, // ★ Item側Port
    ) {
    }

    public function execute(int $viewerUserId): array
    {
        $rows = $this->favoriteItems->listByUserId($viewerUserId);

        return collect($rows)->map(function (array $row) use ($viewerUserId) {

            $itemId = (int) ($row['id'] ?? 0);

            $favoritesCount = $this->favoriteRepository->countByTarget(
                new FavoriteTargetId($itemId)
            );

            return PublicItemAssembler::fromReadModel(
                row: $row,
                viewerUserId: $viewerUserId,
                viewerShopIds: [],
                isFavorited: true,
                favoritesCount: $favoritesCount,
            );
        })->values()->all();
    }
}

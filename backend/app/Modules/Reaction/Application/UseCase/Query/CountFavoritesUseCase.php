<?php


namespace App\Modules\Reaction\Application\UseCase\Query;


use App\Modules\Reaction\Domain\Repository\FavoriteRepository;
use App\Modules\Reaction\Domain\ValueObject\FavoriteTargetId;

final class CountFavoritesUseCase
{
    public function __construct(private readonly FavoriteRepository $favorites)
    {
    }

    public function execute(int $itemId): int
    {
        return $this->favorites->countByTarget(new FavoriteTargetId($itemId));
    }
}

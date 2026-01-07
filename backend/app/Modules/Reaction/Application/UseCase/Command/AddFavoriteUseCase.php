<?php


namespace App\Modules\Reaction\Application\UseCase\Command;


use App\Modules\Reaction\Domain\Entity\Favorite;
use App\Modules\Reaction\Domain\Repository\FavoriteRepository;
use App\Modules\Reaction\Domain\ValueObject\ReactorId;
use App\Modules\Reaction\Domain\ValueObject\FavoriteTargetId;

final class AddFavoriteUseCase
{
    public function __construct(private readonly FavoriteRepository $favorites)
    {
    }

    public function execute(int $userId, int $itemId, ?int $shopId = null): void
    {
        $favorite = new Favorite(
            reactorId: new ReactorId($userId),
            targetId: new FavoriteTargetId($itemId),
            shopId: $shopId,
        );

        $this->favorites->add($favorite);
    }
}

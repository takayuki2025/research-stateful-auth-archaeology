<?php


namespace App\Modules\Reaction\Application\UseCase\Query;


use App\Modules\Reaction\Domain\Repository\FavoriteRepository;
use App\Modules\Reaction\Domain\ValueObject\ReactorId;
use App\Modules\Reaction\Domain\ValueObject\FavoriteTargetId;

final class IsFavoritedUseCase
{
    public function __construct(private readonly FavoriteRepository $favorites)
    {
    }

    public function execute(int $userId, int $itemId): bool
    {
        return $this->favorites->exists(
            new ReactorId($userId),
            new FavoriteTargetId($itemId)
        );
    }
}

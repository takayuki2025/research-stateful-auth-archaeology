<?php


namespace App\Modules\Reaction\Application\UseCase\Command;


use App\Modules\Reaction\Domain\Repository\FavoriteRepository;
use App\Modules\Reaction\Domain\ValueObject\ReactorId;
use App\Modules\Reaction\Domain\ValueObject\FavoriteTargetId;

final class RemoveFavoriteUseCase
{
    public function __construct(private readonly FavoriteRepository $favorites)
    {
    }

    public function execute(int $userId, int $itemId): void
    {
        $this->favorites->remove(
            new ReactorId($userId),
            new FavoriteTargetId($itemId)
        );
    }
}

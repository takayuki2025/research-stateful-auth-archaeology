<?php

namespace App\Modules\Reaction\Domain\Entity;

use App\Modules\Reaction\Domain\ValueObject\ReactorId;
use App\Modules\Reaction\Domain\ValueObject\FavoriteTargetId;

final class Favorite
{
    public function __construct(
        private readonly ReactorId $reactorId,
        private readonly FavoriteTargetId $targetId,
        private readonly ?int $shopId = null,
    ) {
    }

    public function reactorId(): ReactorId
    {
        return $this->reactorId;
    }

    public function targetId(): FavoriteTargetId
    {
        return $this->targetId;
    }

    public function shopId(): ?int
    {
        return $this->shopId;
    }
}

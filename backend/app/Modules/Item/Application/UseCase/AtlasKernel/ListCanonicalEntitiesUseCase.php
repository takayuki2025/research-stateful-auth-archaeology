<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use App\Modules\Item\Domain\Repository\CanonicalEntityRepository;

final class ListCanonicalEntitiesUseCase
{
    public function __construct(
        private CanonicalEntityRepository $repo
    ) {}

    public function handle(string $type): array
    {
        return $this->repo->listByType($type);
    }
}

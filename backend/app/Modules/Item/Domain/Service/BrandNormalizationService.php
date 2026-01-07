<?php

namespace App\Modules\Item\Domain\Service;

use App\Modules\Item\Domain\Repository\BrandRepository;
use App\Modules\Item\Domain\Entity\Brand;

final class BrandNormalizationService
{
    public function __construct(
        private BrandRepository $brandRepository
    ) {
    }

    /**
     * @return Brand[]
     */
    public function normalize(array $rawBrands): array
    {
        $results = [];

        foreach ($rawBrands as $raw) {
            $results[] = $this->brandRepository->resolve($raw);
        }

        return $results;
    }
}

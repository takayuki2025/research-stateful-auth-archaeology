<?php

namespace App\Modules\Item\Domain\Repository;

use App\Modules\Item\Domain\Entity\Brand;

interface BrandRepository
{
    public function resolve(string $raw): Brand;
}

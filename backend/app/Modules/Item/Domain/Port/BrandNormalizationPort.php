<?php

namespace App\Modules\Item\Domain\Port;

use App\Modules\Item\Domain\ValueObject\BrandName;
use App\Modules\Item\Domain\Dto\BrandNormalizationResult;

interface BrandNormalizationPort
{
    public function normalize(BrandName $raw): BrandNormalizationResult;
}
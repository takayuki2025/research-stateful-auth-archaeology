<?php

namespace App\Modules\Item\Infrastructure\External;

use App\Modules\Item\Domain\Port\BrandNormalizationPort;
use App\Modules\Item\Domain\ValueObject\BrandName;
use App\Modules\Item\Domain\Dto\BrandNormalizationResult;

final class AsyncBrandNormalizationAdapter implements BrandNormalizationPort
{
    public function normalize(BrandName $raw): BrandNormalizationResult
    {
        dispatch(new NormalizeBrandJob($raw->raw()));

        return new BrandNormalizationResult('', 0.0, null);
    }
}
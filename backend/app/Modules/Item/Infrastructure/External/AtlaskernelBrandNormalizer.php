<?php

namespace App\Modules\Item\Infrastructure\External;

use App\Modules\Item\Domain\Port\BrandNormalizationPort;
use App\Modules\Item\Domain\ValueObject\BrandName;
use App\Modules\Item\Domain\Dto\BrandNormalizationResult;
use Illuminate\Support\Facades\Http;

final class AtlaskernelBrandNormalizer implements BrandNormalizationPort
{
    public function normalize(BrandName $raw): BrandNormalizationResult
    {
        $response = Http::post(
            config('atlaskernel.endpoint') . '/normalize/brand',
            ['brand' => $raw->raw()]
        );

        $data = $response->json();

        return new BrandNormalizationResult(
            canonicalName: $data['canonical'],
            confidence: (float) $data['confidence'],
            matchedFrom: 'atlaskernel_t.k_v1',
        );
    }
}

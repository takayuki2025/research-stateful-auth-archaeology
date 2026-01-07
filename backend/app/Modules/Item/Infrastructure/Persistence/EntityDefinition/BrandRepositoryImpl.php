<?php

namespace App\Modules\Item\Infrastructure\Persistence\EntityDefinition;

use App\Models\BrandEntity as EloquentBrand;
use App\Modules\Item\Domain\Entity\Brand;
use App\Modules\Item\Domain\Repository\BrandRepository;
use Illuminate\Support\Str;

final class BrandRepositoryImpl implements BrandRepository
{
    public function resolve(string $raw): Brand
    {
        $canonical = Str::lower(trim($raw));

        $entity = EloquentBrand::where('canonical_name', $canonical)->first();

        if (! $entity) {
            $entity = EloquentBrand::create([
                'canonical_name' => $canonical,
                'display_name'   => $raw,
                'created_from'   => 'manual',
            ]);
        }

        return new Brand(
            id: $entity->id,
            canonicalName: $entity->canonical_name,
            displayName: $entity->display_name,
            confidence: (float) $entity->confidence,
        );
    }
}

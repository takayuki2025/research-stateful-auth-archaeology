<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandEntity extends Model
{
    protected $table = 'brand_entities';

    protected $fillable = [
        'normalized_key',
        'canonical_name',
        'display_name',
        'synonyms_json',
        'merged_to_id',
        'is_primary',
        'confidence',
        'created_from',
    ];

    protected $casts = [
        'synonyms_json' => 'array',
        'confidence' => 'float',
    ];
}

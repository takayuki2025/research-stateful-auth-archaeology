<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandEntity extends Model
{
    protected $table = 'brand_entities';

    protected $fillable = [
        'canonical_name',
        'normalized_key',
        'synonyms_json',
        'confidence',
        'created_from',
    ];

    protected $casts = [
        'synonyms_json' => 'array',
        'confidence' => 'float',
    ];
}

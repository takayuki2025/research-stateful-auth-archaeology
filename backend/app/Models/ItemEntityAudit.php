<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemEntityAudit extends Model
{
    protected $fillable = [
        'item_entity_id',
        'decision',
        'confidence',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'confidence' => 'float',
    ];
}

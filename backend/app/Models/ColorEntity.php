<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColorEntity extends Model
{
    protected $table = 'color_entities';

    protected $fillable = [
        'canonical_name',
        'display_name',
        'confidence',
        'created_from',
    ];

    public $timestamps = true;
}

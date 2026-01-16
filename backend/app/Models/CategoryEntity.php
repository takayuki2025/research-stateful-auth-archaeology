<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryEntity extends Model
{
    protected $table = 'category_entities';

    protected $fillable = [
        'canonical_name',
        'display_name',
        'parent_id',
        'confidence',
        'created_from',

    ];

    public $timestamps = true;
}

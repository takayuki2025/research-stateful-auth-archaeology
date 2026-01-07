<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConditionEntity extends Model
{
    protected $table = 'condition_entities';

    protected $fillable = [
        'canonical_name',
        'display_name',
        'confidence',
        'created_from',
    ];

    public $timestamps = true;
}

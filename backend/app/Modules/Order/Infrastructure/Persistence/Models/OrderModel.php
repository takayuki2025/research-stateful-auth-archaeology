<?php

namespace App\Modules\Order\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

final class OrderModel extends Model
{
    protected $table = 'orders';

    protected $guarded = [];

    protected $casts = [
        'items_snapshot'        => 'array',
        'meta'                  => 'array',
        'address_snapshot'      => 'array',
        'address_confirmed_at'  => 'datetime',
    ];
}

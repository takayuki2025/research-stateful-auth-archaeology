<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'order_number',
        'shop_id',
        'user_id',
        'status',
        'total_amount',
        'currency',
        'items_snapshot',
        'address_snapshot',
        'placed_at',
        'address_confirmed_at',
        'paid_at',
        'cancelled_at',
        'refunded_at',
        'meta',
    ];

    protected $casts = [
        'items_snapshot' => 'array',
        'address_snapshot' => 'array',
        'meta' => 'array',

        'placed_at' => 'datetime',
        'address_confirmed_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];
}
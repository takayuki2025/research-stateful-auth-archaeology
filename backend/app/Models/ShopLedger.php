<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class ShopLedger extends Model
{
    protected $table = 'shop_ledgers';

    protected $fillable = [
        'shop_id',
        'type',
        'amount',
        'currency',
        'order_id',
        'payment_id',
        'meta',
        'occurred_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'occurred_at' => 'datetime',
    ];

    public $timestamps = false;
}

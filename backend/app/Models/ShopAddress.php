<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class ShopAddress extends Model
{
    protected $table = 'shop_addresses';

    protected $fillable = [
        'shop_id',
        'postal_code',
        'prefecture',
        'city',
        'address_line1',
        'address_line2',
        'recipient_name',
        'phone',
    ];
}

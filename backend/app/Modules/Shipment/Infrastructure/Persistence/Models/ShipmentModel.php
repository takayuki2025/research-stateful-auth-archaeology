<?php

namespace App\Modules\Shipment\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

final class ShipmentModel extends Model
{
    protected $table = 'shipments';

    protected $fillable = [
        'order_id',
        'status',
        'eta',
    ];

    protected $casts = [
        'eta' => 'datetime',
    ];
}

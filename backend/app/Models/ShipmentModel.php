<?php

namespace App\Modules\Shipment\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

final class ShipmentModel extends Model
{
    use HasFactory;

    protected $table = 'shipments';

    protected $fillable = [
        'shop_id',
        'order_id',
        'status',
        'eta',
    ];

    protected $casts = [
        'eta' => 'date',
    ];
}

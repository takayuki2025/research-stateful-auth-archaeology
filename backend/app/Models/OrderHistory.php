<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class OrderHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'order_id',
        'item_id',
        'user_id',

        // snapshot
        'item_name',
        'item_image',
        'price_amount',
        'price_currency',
        'payment_method',
        'buy_address',
        'quantity',
    ];

    protected $casts = [
        'buy_address' => 'array',
    ];

    // 🔽 関係は「あってもなくてもいい」
    // Query 用なので必須ではない

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}

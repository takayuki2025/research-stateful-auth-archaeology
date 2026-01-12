<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopRole extends Model
{
    protected $table = 'shop_roles';

    protected $fillable = [
        'user_id',
        'shop_id',
        'shop_code',
        'role',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

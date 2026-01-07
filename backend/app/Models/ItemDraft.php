<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class ItemDraft extends Model
{
    protected $table = 'item_drafts';

    // ★ これが無いと UUID は 100% 壊れる
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',          // ★ id も fillable に入れる
        'user_id',
        'shop_id',
        'seller_id',
        'name',
        'price',
        'brand',
        'status',
        'item_image',
        'explain',
        'condition',
        'category',
        'remain',
    ];

    protected $casts = [
        'category' => 'array',
    ];
}
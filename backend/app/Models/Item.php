<?php

namespace App\Models;

use App\Models\ItemEntityTag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_origin',
        'shop_id',
        'created_by_user_id',
        'published_at',
        'name',
        'price',
        'brand',
        'explain',
        'condition',
        'category',
        'item_image',
        'remain',
    ];

    protected $casts = [
        'category' => 'array',
    ];

    /** 画像URLアクセサ（既存コードそのまま） */
    public function getItemImageAttribute($value): string
    {
        return $value ?? '';
    }

    /** 出品者 */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** ⭐ お気に入り */
    public function favorites(): HasMany
    {
        return $this->hasMany(Good::class);
    }

    /** コメント */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** カート */
    public function usersInCart()
    {
        return $this->belongsToMany(User::class, 'cart_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /** ショップ */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    // ==================================================
    // 🔥 AtlasKernel 用リレーション（ここを追加）
    // ==================================================

    /** すべての解析エンティティ履歴 */
    public function entities(): HasMany
    {
        return $this->hasMany(
            ItemEntity::class,
            'item_id'
        );
    }

    /** 最新の解析結果（表示用） */
    public function latestEntity(): HasOne
    {
        return $this->hasOne(
            ItemEntity::class,
            'item_id'
        )->where('is_latest', true);
    }

    /** 抽出タグ（brand / color / condition など） */
    public function entityTags(): HasMany
    {
        return $this->hasMany(
            ItemEntityTag::class,
            'item_id'
        );
    }

    /* =====================
     * Semantic Helpers（重要）
     * ===================== */

    public function isUserPersonal(): bool
    {
        return $this->item_origin === 'USER_PERSONAL';
    }

    public function isShopManaged(): bool
    {
        return $this->item_origin === 'SHOP_MANAGED';
    }
}

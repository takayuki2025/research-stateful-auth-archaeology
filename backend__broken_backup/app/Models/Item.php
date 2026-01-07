<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // 追加

class Item extends Model
{
    use HasFactory;

    /**
     * プライマリキー
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 複数代入可能な属性 (テストが期待するカラム名に修正)
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'price',
        
        // 🌟 修正: テストが期待するカラム名 (explain, condition, category, item_image, brand, remain) に合わせる 🌟
        'explain',      
        'condition',    
        'category',     
        'item_image',   
        'brand',        
        'remain',       
    ];
    
    /**
     * ネイティブタイプへキャストする属性
     * categoryフィールドの配列/JSON変換を復活させます。
     * @var array
     */
    protected $casts = [
        'category' => 'array',
    ];


    /**
     * モデルが属するUserを取得します。
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 商品に付けられたGoodを取得します。
     * @return HasMany
     */
    public function goods(): HasMany
    {
        return $this->hasMany(Good::class);
    }

    /**
     * 商品に付けられたCommentを取得します。
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
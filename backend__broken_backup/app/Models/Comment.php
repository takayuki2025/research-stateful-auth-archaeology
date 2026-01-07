<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments';

    /**
     * マスアサインメント時に設定可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', // コメント投稿者ID
        'item_id', // 商品ID
        'comment', // コメント本文
    ];

    // --- リレーションシップの定義 ---

    /**
     * このコメントを投稿したユーザー (User) を取得します。
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * このコメントが付けられた商品 (Item) を取得します。
     *
     * @return BelongsTo
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
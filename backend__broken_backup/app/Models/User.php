<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * マスアサインメント時に設定可能な属性。
     * usersテーブルのカラム（カスタムカラムを含む）に基づいています。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'post_number',
        'address',
        'building',
        'user_image',
        'address_country',
    ];

    /**
     * 配列に含めない属性。
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * ネイティブタイプにキャストする必要がある属性。
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // --- リレーションシップの定義 ---

    /**
     * このユーザーが出品した商品 (Item) を取得します。
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * このユーザーの購入履歴 (OrderHistory) を取得します。
     *
     * @return HasMany
     */
    public function orderHistories(): HasMany
    {
        return $this->hasMany(OrderHistory::class);
    }

    /**
     * このユーザーが付けた「いいね」 (Good) を取得します。
     *
     * @return HasMany
     */
    public function goods(): HasMany
    {
        return $this->hasMany(Good::class);
    }

    /**
     * このユーザーが投稿したコメント (Comment) を取得します。
     *
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\RoleUser;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// use Illuminate\Auth\Notifications\VerifyEmail; // sendEmailVerificationNotificationで使用　

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',

        // 🔥 状態フラグ
        'profile_completed',
        'first_login_at',
        'email_verified_at',

        // Firebase / Tenant
        'firebase_uid',
        'shop_id',

        // ⚠️ 旧住所系（将来削除予定）
        'post_number',
        'address',
        'building',
        'address_country',
        'user_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'   => 'datetime',
        'first_login_at'      => 'datetime',
        'profile_completed'   => 'boolean',
        'password'            => 'hashed',
        'shop_id'             => 'integer',
    ];

    /* ============================================================
       🔐 Profile Completion 判定（Domain）
    ============================================================ */

    public function markProfileCompleted(): void
    {
        if (! $this->profile_completed) {
            $this->profile_completed = true;
            $this->save();
        }
    }

    public function resetProfileCompleted(): void
    {
        if ($this->profile_completed) {
            $this->profile_completed = false;
            $this->save();
        }
    }

    // --- リレーションシップの定義 (既存のまま) ---

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function orderHistories(): HasMany
    {
        return $this->hasMany(OrderHistory::class);
    }

    public function goods(): HasMany
    {
        return $this->hasMany(Good::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }


    public function cartItems()
    {
        // ユーザーは複数のカートアイテムを持つ (中間テーブル cart_items を介する)
        return $this->hasMany(CartItem::class);
    }

    // ユーザーがカートに入れている商品を直接取得したい場合 (Many-to-Many)
    public function cart()
    {
        // itemsテーブルと関連付け
        return $this->belongsToMany(Item::class, 'cart_items')
                    ->withPivot('quantity') // cart_itemsテーブルのquantityカラムも取得
                    ->withTimestamps();
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(
            Shop::class,
            'role_user',
            'user_id',
            'shop_id'
        )->withPivot('role_id')
         ->withTimestamps();
    }

    public function formattedRoles(): array
    {
        return $this->roles()->get()->map(function ($role) {
            return [
                'id'      => $role->id,
                'name'    => $role->name,
                'slug'    => $role->slug,
                'shop_id' => $role->pivot->shop_id,
            ];
        })->toArray();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)
            ->using(RoleUser::class)
            ->withPivot('shop_id')
            ->withTimestamps();
    }

    public function shopRoles()
    {
        return $this->hasMany(RoleUser::class);
    }



    public function rolesForShop(?int $shopId)
    {
        return $this->roles()
            ->when(
                $shopId !== null,
                fn ($q) => $q->where('role_user.shop_id', $shopId)
            )
            ->get();
    }

    public function hasRole(string $slug, ?int $shopId = null): bool
    {
        return $this->roles()
            ->where('roles.slug', $slug)
            ->when(
                $shopId !== null,
                fn ($q) => $q->where('role_user.shop_id', $shopId)
            )
            ->exists();
    }

    public function assignRole(int $roleId, ?int $shopId = null)
    {
        $this->roles()->attach($roleId, ['shop_id' => $shopId]);
    }

    public function removeRole(int $roleId, ?int $shopId = null)
    {
        $this->roles()
            ->wherePivot('shop_id', $shopId)
            ->detach($roleId);
    }

    public function refreshTokens()
    {
        return $this->hasMany(\App\Models\RefreshToken::class);
    }

    /**
     * メール検証通知をユーザーに送信します。
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        // ★★★ インポートしたクラス名を使用する ★★★
        $this->notify(new CustomVerifyEmail());
    }

}

<?php

namespace App\Modules\Auth\Presentation\Http\Presenters;

use App\Models\User;

final class AuthUserPresenter
{
    public static function fromModel(User $user): array
    {
        // 🔹 店舗ロール取得（新規登録直後は空）
        $shopRoles = $user->shopRoles()
            ->with(['shop', 'role'])
            ->get()
            ->map(fn ($r) => [
                'shop_id'   => $r->shop_id,
                'shop_code' => $r->shop?->shop_code, // ★ null safe
                'role'      => $r->role->slug,       // owner / manager / staff
            ])
            ->values();

        // 🔹 primary shop（UI の初期遷移用）
        $primaryShop = $shopRoles->first();

        return [
            // =========================
            // 🧍 User 基本情報
            // =========================
            'id'                => $user->id,
            'name'              => $user->name,
            'email'             => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'first_login_at'    => $user->first_login_at,

            // =========================
            // 🔐 グローバルロール
            // =========================
            'roles' => $user->roles()
                ->wherePivotNull('shop_id')
                ->pluck('slug')
                ->values(),

            // =========================
            // 🏪 Shop 関連（超重要）
            // =========================
            'has_shop' => $shopRoles->isNotEmpty(),

            // UI が「最初にどこへ遷移するか」判断するため
            'primary_shop' => $primaryShop ? [
                'shop_id'   => $primaryShop['shop_id'],
                'shop_code' => $primaryShop['shop_code'],
                'role'      => $primaryShop['role'],
            ] : null,

            // 全ショップロール一覧（0件でもOK）
            'shop_roles' => $shopRoles,
        ];
    }
}

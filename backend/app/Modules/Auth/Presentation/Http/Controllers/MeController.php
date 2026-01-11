<?php

namespace App\Modules\Auth\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        /**
         * shop付きロールを取得
         * - shop_id は pivot（role_user）にある
         * - フロント判定のため role は slug（owner/manager/staff）を返す
         */
        $roles = $user->roles()
            ->wherePivotNotNull('shop_id')
            ->get([
                'roles.id',
                'roles.slug', // ★ここが本命
                'roles.name', // 任意：表示に使いたいなら残してもOK
            ]);

        $shopIds = $roles
            ->pluck('pivot.shop_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        // shop_id -> shop_code の辞書
        $shopCodeById = empty($shopIds)
            ? collect()
            : Shop::query()
                ->whereIn('id', $shopIds)
                ->get(['id', 'shop_code'])
                ->keyBy('id')
                ->map(fn ($s) => $s->shop_code);

        // shop_roles を整形（shop_code が取れないものは除外）
        $shopRoles = $roles
            ->map(function ($role) use ($shopCodeById) {
                $shopId = (int) $role->pivot->shop_id;
                $shopCode = $shopCodeById->get($shopId);

                if (! $shopCode) {
                    return null; // shop が消えた等で code が取れない場合は落とす
                }

                return [
                    'shop_id'   => $shopId,
                    'shop_code' => $shopCode,
                    'role'      => $role->slug, // ★ owner | manager | staff を返す
                    // 'role_name' => $role->name, // 任意：表示用途が欲しければ
                ];
            })
            ->filter() // null を除去
            ->values();

        return response()->json([
            'id'                => $user->id,
            'email'             => $user->email,
            'display_name'      => $user->name,
            'email_verified_at' => $user->email_verified_at,
            'profile_completed' => (bool) $user->profile_completed,
            'shop_roles'        => $shopRoles,
        ]);
    }
}
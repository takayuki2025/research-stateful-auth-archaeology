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

        $roles = $user->roles()
            ->wherePivotNotNull('shop_id')
            ->get(['roles.slug']);

        $shopIds = $roles->pluck('pivot.shop_id')->unique()->values();

        $shopCodeById = \App\Models\Shop::query()
            ->whereIn('id', $shopIds)
            ->pluck('shop_code', 'id');

        $shopRoles = $roles->map(fn ($role) => [
            'shop_id'   => (int) $role->pivot->shop_id,
            'shop_code' => $shopCodeById[$role->pivot->shop_id] ?? null,
            'role'      => $role->slug, // ★ slug固定
        ])->values();

        return response()->json([
            'id'                => $user->id,
            'email'             => $user->email,
            'display_name'      => $user->name,
            'email_verified_at' => $user->email_verified_at,
            'profile_completed' => $user->profile_completed,
            'shop_roles'        => $shopRoles,
        ]);
    }
}
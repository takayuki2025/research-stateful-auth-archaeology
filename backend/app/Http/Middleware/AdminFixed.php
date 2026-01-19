<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AdminFixed
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // 0) 暫定 allowlist（緊急ブレーキ）
        $allow = config('trustledger.admin_user_ids', []);
        if (!in_array((int)$user->id, $allow, true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // 1) 許可ロール（roles.id 6..11）を持っているか（グローバルロールのみ shop_id NULL）
        $allowedRoleIds = [6,7,8,9,10,11];

        $hasAllowedRole = DB::table('role_user')
            ->where('user_id', (int)$user->id)
            ->whereNull('shop_id')
            ->whereIn('role_id', $allowedRoleIds)
            ->exists();

        if (! $hasAllowedRole) {
            return response()->json(['message' => 'Forbidden (role)'], 403);
        }

        return $next($request);
    }
}
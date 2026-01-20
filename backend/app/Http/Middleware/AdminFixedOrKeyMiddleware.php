<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class AdminFixedOrKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 1) X-Admin-Key 認証（Rails向け）
        $expectedKey = (string) env('TRUSTLEDGER_ADMIN_X_ADMIN_KEY', '');
        $givenKey = (string) $request->header('X-Admin-Key', '');

        if ($expectedKey !== '' && hash_equals($expectedKey, $givenKey)) {
            // 監査・ログ相関のため、誰として通したかを残す（任意）
            $request->attributes->set('admin_actor', 'x-admin-key');
            return $next($request);
        }

        // 2) 既存の固定Admin（人間ログイン運用）も残す
        $allowed = collect(explode(',', (string) env('TRUSTLEDGER_ADMIN_USER_IDS', '')))
            ->map(fn ($v) => (int) trim($v))
            ->filter(fn ($v) => $v > 0)
            ->values()
            ->all();

        $userId = Auth::id();
        if (is_int($userId) && in_array($userId, $allowed, true)) {
            $request->attributes->set('admin_actor', 'user:' . $userId);
            return $next($request);
        }

        return response()->json([
            'message' => 'Admin authorization failed.',
        ], 403);
    }
}
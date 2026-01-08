<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class OptionalSanctumAuth
{
    public function handle(Request $request, Closure $next)
    {
        // ここで "sanctum" guard を試す（失敗しても弾かない）
        try {
            if (Auth::guard('sanctum')->check()) {
                Auth::shouldUse('sanctum');
            }
        } catch (\Throwable $e) {
            // optional は握りつぶす（ログインしていないのと同じ扱い）
        }

        return $next($request);
    }
}
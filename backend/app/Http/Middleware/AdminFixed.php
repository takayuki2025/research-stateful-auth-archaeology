<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class AdminFixed
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $allow = config('trustledger.admin_user_ids', []);
        if (!in_array((int)$user->id, $allow, true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoleMiddleware
{
    /**
     * 使用例：
     *  Route::middleware(['auth.jwt', 'role:admin'])->get(...);
     */
    public function handle(Request $request, Closure $next, string $roleSlug): JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        if (! $user->hasRole($roleSlug)) {
            return response()->json([
                'message' => 'Forbidden: role ' . $roleSlug . ' is required',
            ], 403);
        }

        return $next($request);
    }
}

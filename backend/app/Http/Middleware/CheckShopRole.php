<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Modules\Shop\Domain\Entity\Shop;

final class CheckShopRole
{
    /**
     * 例: 'shop.role:owner,manager,staff'
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        /** @var Shop|null $shop */
        $shop = $request->attributes->get('currentShop');
        $user = $request->user();

        if (!$shop || !$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $roleList = array_values(
            array_filter(array_map('trim', explode(',', $roles)))
        );

        if ($roleList === []) {
            return response()->json(['error' => 'Role config invalid'], 500);
        }

        foreach ($roleList as $role) {
            if ($user->hasRole($role, $shop->id())) {
                return $next($request);
            }
        }

        return response()->json(['error' => 'Permission denied'], 403);
    }
}

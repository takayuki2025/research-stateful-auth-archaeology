<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class ResolveTenant
{
    public function handle(Request $request, Closure $next)
    {
        $tenantId = $request->attributes->get('tenant_id');

        if (!$tenantId) {
            // ★ 401 ではなく 400/403
            abort(400, 'tenant_id not resolved');
        }

        return $next($request);
    }
}

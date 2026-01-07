<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Modules\Shop\Domain\Entity\Shop;

final class AddTenantInfoToLogs
{
    public function handle(Request $request, Closure $next)
    {
        /** @var Shop|null $shop */
        $shop = $request->attributes->get('currentShop');

        if ($shop) {
            Log::withContext([
                'shop_id'   => $shop->id(),
                'shop_code' => $shop->shopCode(),
            ]);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequestLogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);
        $response = $next($request);
        $end = microtime(true);

        $log = [
            'service' => 'laravel-auth',
            'method' => $request->method(),
            'path'   => $request->path(),
            'ip'     => $request->ip(),
            'latency_ms' => round(($end - $start) * 1000, 2),
            'user_id' => optional($request->user())->id,
            'tenant_id' => $request->attributes->get('tenant_id'),
            'status' => $response->getStatusCode(),
        ];

        Log::info(json_encode($log));

        return $response;
    }
}

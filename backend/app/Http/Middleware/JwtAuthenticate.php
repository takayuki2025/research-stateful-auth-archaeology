<?php

namespace App\Http\Middleware;

use App\Modules\Auth\Application\Service\JwtUserResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Modules\Auth\Application\Context\AuthContext;


final class JwtAuthenticate
{
    public function __construct(
        private JwtUserResolver $resolver,
        private AuthContext $authContext,
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        $resolved = $this->resolver->resolve($request);

        if (!$resolved) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        Auth::setUser($resolved['user']);
        $request->setUserResolver(fn () => $resolved['user']);

        // ★ 唯一の注入口
        $this->authContext->setPrincipal($resolved['principal']);

        return $next($request);
    }
}

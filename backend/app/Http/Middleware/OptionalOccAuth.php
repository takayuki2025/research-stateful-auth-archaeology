<?php

namespace App\Http\Middleware;

use App\Modules\Auth\Application\Context\AuthContext;
use App\Modules\Auth\Application\Service\JwtUserResolver;
use App\Modules\Auth\Domain\ValueObject\AuthPrincipal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class OptionalOccAuth
{
    public function __construct(
        private JwtUserResolver $jwt,
        private AuthContext $authContext,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $this->authContext->clear();

        $resolved = $this->jwt->resolve($request);
        if ($resolved) {
            Auth::setUser($resolved['user']);
            $request->setUserResolver(fn () => $resolved['user']);
            $this->authContext->setPrincipal($resolved['principal']);
            return $next($request);
        }

        $user = Auth::guard('sanctum')->user();
        if ($user) {
            $principal = AuthPrincipal::fromSanctumUser($user);
            $this->authContext->setPrincipal($principal);
        }

        return $next($request);
    }
}
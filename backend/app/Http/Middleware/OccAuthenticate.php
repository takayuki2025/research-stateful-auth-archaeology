<?php

namespace App\Http\Middleware;

use App\Modules\Auth\Application\Context\AuthContext;
use App\Modules\Auth\Application\Service\JwtUserResolver;
use App\Modules\Auth\Domain\ValueObject\AuthPrincipal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class OccAuthenticate
{
    public function __construct(
        private JwtUserResolver $jwt,
        private AuthContext $authContext,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $this->authContext->clear();

        // 1) JWT Bearer 優先
        $resolved = $this->jwt->resolve($request);

\Log::info('[🔥OccAuthenticate] jwt_resolved', [
  'has_authz' => $request->headers->has('Authorization'),
  'resolved' => (bool)$resolved,
]);

        if ($resolved) {
            Auth::setUser($resolved['user']);
            $request->setUserResolver(fn () => $resolved['user']);
            $this->authContext->setPrincipal($resolved['principal']);
            return $next($request);
        }

        // 2) Sanctum session
        $user = Auth::guard('sanctum')->user();
        if ($user) {
            $principal = AuthPrincipal::fromSanctumUser($user);
            $this->authContext->setPrincipal($principal);
            return $next($request);
        }

        return response()->json(['message' => 'Unauthenticated'], 401);
    }
}
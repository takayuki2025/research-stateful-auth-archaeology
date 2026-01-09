<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Modules\Auth\Application\Context\AuthContext;
use App\Modules\Auth\Domain\ValueObject\AuthPrincipal;

final class SetAuthPrincipalFromSanctum
{
    public function __construct(
        private AuthContext $authContext
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user(); // Sanctum

        if ($user) {
            $principal = AuthPrincipal::fromSanctumUser($user);
            $this->authContext->setPrincipal($principal);
        }

        return $next($request);
    }
}
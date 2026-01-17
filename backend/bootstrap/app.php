<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;
return Application::configure(basePath: dirname(__DIR__))

    /*
    |--------------------------------------------------------------------------
    | Service Providers
    |--------------------------------------------------------------------------
    */
    ->withProviders([
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\AuthContextServiceProvider::class,

        App\Modules\Shop\ShopServiceProvider::class,
        App\Modules\Item\Infrastructure\Providers\ItemModuleServiceProvider::class,
        App\Modules\Item\Infrastructure\Providers\ItemEventServiceProvider::class,
        App\Modules\Item\Infrastructure\Providers\ItemServiceProvider::class,
        App\Modules\Shipment\Infrastructure\Providers\ShipmentServiceProvider::class,
        App\Modules\Review\Infrastructure\ReviewServiceProvider::class,
        App\Modules\AtlasKernel\Infrastructure\Providers\AtlasServiceProvider::class,
    ])

    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    */
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    /*
    |--------------------------------------------------------------------------
    | Middleware（Laravel 11）
    |--------------------------------------------------------------------------
    */
    ->withMiddleware(function (Middleware $middleware) {

        /*
         * Global middleware
         */
        $middleware->append(\App\Http\Middleware\RequestLogMiddleware::class);
        $middleware->append(\App\Http\Middleware\AddTenantInfoToLogs::class);

        /*
         * Proxies
         */
        $middleware->trustProxies(
            at: explode(',', env(
                'TRUSTED_PROXIES',
                '10.0.0.0/8,172.16.0.0/12,192.168.0.0/16'
            )),
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB
                | Request::HEADER_FORWARDED
        );

        /*
         * CSRF
         * - SPA(Sanctum)でも web login を使うなら /login は web 扱いになることがある
         * - ただしあなたの設計は「Next.js -> /login を叩く」ので CSRF が必要
         * - api/* は除外のままでOK
         */
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        /*
         * Aliases
         */
        $middleware->alias([
            'auth'              => \App\Http\Middleware\Authenticate::class,
            'verified'          => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'throttle'          => \Illuminate\Routing\Middleware\ThrottleRequests::class,

            // Sanctum
            'sanctum.auth'      => \Laravel\Sanctum\Http\Middleware\Authenticate::class,
            'auth.sanctum.optional' => \App\Http\Middleware\OptionalSanctumAuth::class,
            // Tenant
            'tenant'            => \App\Http\Middleware\ResolveTenant::class,

            // JWT
            'auth.jwt'          => \App\Http\Middleware\JwtAuthenticate::class,
            'auth.jwt.optional' => \App\Http\Middleware\OptionalJwtAuth::class,

            // Roles
            'role'              => \App\Http\Middleware\RoleMiddleware::class,
            'shop.role'         => \App\Http\Middleware\CheckShopRole::class,
            'shop.context'      => \App\Modules\Shop\Presentation\Http\Middleware\ShopContextMiddleware::class,
        ]);

        /*
         * API group
         * - EnsureFrontendRequestsAreStateful を「先頭」に置くのが基本
         * - SubstituteBindings は1回でOK（重複していたので整理）
         */
        $middleware->api(
            prepend: [
                \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
                \App\Http\Middleware\SetAuthPrincipalFromSanctum::class,
            ],
            append: [
                'throttle:api',
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
            ]
        );

        /*
         * もし「web でも未認証を JSON で返したい」なら、
         * ここで Accept: application/json を強制する middleware を global に入れる選択肢もある。
         * ただし通常は例外レンダリング側で api/* を確実に JSON 化すれば十分。
         */
    })

    /*
    |--------------------------------------------------------------------------
    | Exception Handling
    |--------------------------------------------------------------------------
    */
    ->withExceptions(function (Exceptions $exceptions) {

    $exceptions->reportable(function (Throwable $e) {
        logger()->error('FATAL_EXCEPTION', [
            'exception' => get_class($e),
            'message'   => $e->getMessage(),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => $e->getTraceAsString(),
        ]);
    });

    // ✅ 次に API 用 JSON レンダリング
    $exceptions->render(function (Throwable $e, Request $request) {

        $isApi = $request->is('api/*');
        $wantsJson = $request->expectsJson() || $isApi;

        if (!$wantsJson) {
            throw $e;
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'error_type' => 'AuthenticationException',
                'message' => 'Unauthenticated',
            ], 401);
        }

        if ($e instanceof DomainException) {
            return response()->json([
                'error_type' => 'DomainException',
                'message' => $e->getMessage(),
            ], 422);
        }

        if ($e instanceof BindingResolutionException) {
            return response()->json([
                'error_type' => 'BindingResolutionException',
                'message' => 'Service binding error: ' . $e->getMessage(),
            ], 500);
        }

        $statusCode = $e instanceof HttpExceptionInterface
            ? $e->getStatusCode()
            : 500;

        return response()->json([
            'error_type' => get_class($e),
            'message' => $e->getMessage(),
        ], $statusCode);
    });
})

    ->create();
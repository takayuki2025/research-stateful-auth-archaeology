<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Component\HttpFoundation\Response;

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
        App\Modules\Shipment\Infrastructure\Providers\ShipmentServiceProvider::class,
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
    | Middleware（★ここが Laravel 11 の正解位置）
    |--------------------------------------------------------------------------
    */
    ->withMiddleware(function (Middleware $middleware) {

        // Global
        $middleware->append(\App\Http\Middleware\RequestLogMiddleware::class);
        $middleware->append(\App\Http\Middleware\AddTenantInfoToLogs::class);

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

        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        // Alias（★ここで定義しないと一切効かない）
        $middleware->alias([
            'auth'              => \Illuminate\Auth\Middleware\Authenticate::class,
            'sanctum.auth'      => \Laravel\Sanctum\Http\Middleware\Authenticate::class,
            'throttle'          => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified'          => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

            'tenant'            => \App\Http\Middleware\ResolveTenant::class,

            'auth.jwt'          => \App\Http\Middleware\JwtAuthenticate::class,
            'auth.jwt.optional' => \App\Http\Middleware\OptionalJwtAuth::class,

            'role'              => \App\Http\Middleware\RoleMiddleware::class,
            'shop.role'         => \App\Http\Middleware\CheckShopRole::class,
            'shop.context'      => \App\Modules\Shop\Presentation\Http\Middleware\ShopContextMiddleware::class,
        ]);

        // API group
        $middleware->api(
            prepend: [
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                // ✅ ここに Sanctum のステートフル Middleware を追加
                \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
            ],
            append: [
                'throttle:api',
            ]
        );
    })

    /*
    |--------------------------------------------------------------------------
    | Exception Handling
    |--------------------------------------------------------------------------
    */
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (\Throwable $e, Request $request) {

            if (!($request->expectsJson() || $request->is('api/*'))) {
                return null;
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'error_type' => 'AuthenticationException',
                    'message' => 'Unauthenticated',
                ], 401);
            }

            if ($e instanceof \DomainException) {
                return response()->json([
                    'error_type' => 'DomainException',
                    'message' => $e->getMessage(),
                ], 422);
            }

            if ($e instanceof BindingResolutionException) {
                return response()->json([
                    'error_type' => 'BindingResolutionException',
                    'message' => 'Service binding error: ' . $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => basename($e->getFile()),
                ], 500);
            }

            $statusCode = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException
                ? $e->getStatusCode()
                : 500;

            return response()->json([
                'error_type' => get_class($e),
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile()),
            ], $statusCode);
        });
    })

    ->create();
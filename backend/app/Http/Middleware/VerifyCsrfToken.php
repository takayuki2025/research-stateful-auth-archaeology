<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // ★★★ この配列の中に、以下の2行を追加/確認してください ★★★
        'api/firebase/auth',
        'api/*',
        'api/payments/webhook/stripe',
    ];
}

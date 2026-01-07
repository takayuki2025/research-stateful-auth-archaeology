<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class No401Redirect
{
    /**
     * 💡 フロントエンドとの合意に基づくカスタムステータスコード (HTTPではない)
     * 401 Unauthorized 相当を表すために使用。
     */
    public const UNAUTHENTICATED_CODE = 801;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 次のミドルウェア層またはルートのクロージャを実行
        $response = $next($request);

        // 💡 認証ミドルウェア(auth:sanctumなど)が生成した 401 Unauthorized をキャッチ
        if ($response->getStatusCode() === 401) {
            // ステータスコードを 200 OK に上書き (赤字を消すため)
            $response->setStatusCode(200);

            // わかりやすいメッセージと、内部的な追跡用のコードをJSONボディに設定
            $response->setContent(json_encode([
                'authenticated' => false,
                // 💡 ユーザーフレンドリーなメッセージ
                'message' => 'Auth Check: Inactive Session OK to proceed.',
                // 💡 内部的な追跡コードとして、意図的な未認証を意味する独自の数字 (801) を使用
                'status_code_override' => self::UNAUTHENTICATED_CODE
            ]));

            // Content-Type ヘッダーをJSONに設定し直す
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}

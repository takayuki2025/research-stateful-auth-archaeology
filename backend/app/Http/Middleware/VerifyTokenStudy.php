<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use App\Models\User; // あなたのユーザーモデル
use Kreait\Firebase\Auth as FirebaseAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\AuthenticationException;

class VerifyTokenStudy
{
    protected $firebaseAuth;

    // Firebase Authをコンストラクタインジェクション（DI）で受け取る
    public function __construct(FirebaseAuth $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    /**
     * @param string $mode 'sanctum', 'firebase', 'verify_only' のいずれか
     */
    public function handle(Request $request, Closure $next, string $mode): Response
    {
        try {
            switch ($mode) {
                case 'sanctum':
                    $this->handleSanctumAuthentication($request);
                    break;
                case 'firebase':
                    $this->handleFirebaseAuth($request);
                    break;
                case 'verify_only':
                    $this->handleFirebaseVerification($request);
                    break;
                default:
                    throw new \Exception("Invalid study mode: {$mode}");
            }
        } catch (AuthenticationException $e) {
            // 認証失敗時
            return response()->json(['message' => 'Unauthenticated.'], 401);
        } catch (\Exception $e) {
            // その他のエラー
            return response()->json(['message' => 'Server Error.'], 500);
        }

        return $next($request);
    }

    /**
     * 🥇 モード1: SanctumAuthStudy (Sanctum/Cookieの模倣)
     * 責務: Laravelセッション/Cookieの認証を優先し、フォールバックでトークンを試す。
     */
    protected function handleSanctumAuthentication(Request $request): void
    {
        // 1. Laravel標準のセッション/Cookie認証を試みる (既に認証されていればスキップ)
        if (Auth::check()) {
            return;
        }

        // 2. Bearerトークンがあれば、それをSanctum Tokenとして検証する（Sanctumの簡易模倣）
        $token = $request->bearerToken();
        if ($token) {
            // ★実際は SanctumのTokenモデルを参照するロジックが必要だが、今回はCookie認証との比較が目的のため、
            // 簡易的にトークンがあれば認証成功として処理を進めるか、Cookie認証にフォーカスする。

            // 💡 学習ポイント: Sanctum/Sessionの認証はLaravelの認証ガードが担当するため、
            // ここでは**Cookieによる認証状態のチェック**に焦点を当てます。

            // トークンがない場合、Cookie（セッション）で認証が完了しているかどうかをチェックする
            if (!Auth::guard('web')->check() && !Auth::check()) {
                throw new AuthenticationException('Unauthenticated via Sanctum Study.');
            }
            return;
        }

        // トークンもCookieもない場合、認証失敗
        throw new AuthenticationException('Unauthenticated via Sanctum Study.');
    }

    /**
     * 🥈 モード2: FirebaseAuthStudy (Firebaseトークン認証)
     * 責務: Firebase ID Tokenを検証し、Auth::login()を実行する。
     */
    protected function handleFirebaseAuth(Request $request): void
    {
        $idToken = $request->bearerToken();
        if (!$idToken) {
            throw new AuthenticationException('Firebase ID Token required.');
        }

        try {
            // 1. Firebase SDKでトークンを厳格に検証
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($idToken);
            $uid = $verifiedIdToken->claims()->get('sub');

            // 2. UIDに基づいてDBからユーザーをロード
            $user = User::where('uid', $uid)->first();
            if (!$user) {
                throw new AuthenticationException('User not found in DB.');
            }

            // 3. Laravelの認証状態を確立
            Auth::login($user);

        } catch (\Throwable $e) {
            // 検証失敗（トークン期限切れ、不正など）
            throw new AuthenticationException('Invalid Firebase ID Token.', 0, $e);
        }
    }

    /**
     * 🥉 モード3: FirebaseVerifyOnlyStudy (検証のみ)
     * 責務: Firebase ID Tokenの検証のみを行い、認証状態を設定しない。
     */
    protected function handleFirebaseVerification(Request $request): void
    {
        $idToken = $request->bearerToken();
        if (!$idToken) {
            throw new AuthenticationException('Firebase ID Token required for verification.');
        }

        try {
            // 1. トークンの検証のみ実行
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($idToken);

            // 2. 💡 最も重要なポイント: Auth::login() は実行しない！

            // 3. 検証済みのペイロードを次のコントローラーに渡す
            $request->attributes->set('firebase_verified_uid', $verifiedIdToken->claims()->get('sub'));

        } catch (\Throwable $e) {
            throw new AuthenticationException('Verification failed.', 0, $e);
        }
    }
}

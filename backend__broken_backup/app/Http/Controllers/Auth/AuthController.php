<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Requests\RegisterRequest; // カスタムリクエストをインポート
use App\Http\Requests\LoginRequest; // ★ LoginRequestをインポート (修正)
use Illuminate\Validation\ValidationException; // (修正)

/**
 * 認証関連の全般的なカスタム処理を担うコントローラ。
 * (新規登録、ログイン、メール認証など)
 */
class AuthController extends BaseController
{
    // ===============================================
    // ★★★ 新規登録機能 (CustomRegisteredUserControllerから移行) ★★★
    // ===============================================

    /**
     * 新規登録フォームを表示します。
     *
     * @return \Illuminate\View\View
     */
    public function createRegister()
    {
        return view('auth.register');
    }

    /**
     * 新規登録リクエストを処理します。
     *
     * @param  \App\Http\Requests\RegisterRequest  $request // カスタムリクエストを使用
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function storeRegister(RegisterRequest $request)
    {
        // RegisterRequest内で既にカスタムバリデーションが実行される

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Fortifyのredirects('login')の設定に合わせ、onetime.showへリダイレクトします。
        return redirect(route('onetime.show')); 
    }

    // ===============================================
    // ★★★ ログイン機能 (Fortifyから移行/カスタム) ★★★
    // ===============================================

    /**
     * ログインフォームを表示します。
     *
     * @return \Illuminate\View\View
     */
    public function createLogin()
    {
        return view('auth.login');
    }

    /**
     * ログインリクエストを処理します。
     * * @param  \App\Http\Requests\LoginRequest  $request // カスタムリクエストを使用
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function storeLogin(LoginRequest $request)
    {
        // LoginRequest内のバリデーションと認証ロジックを実行
        $request->authenticate(); 

        // セッションを再生成し、ログイン後のリダイレクト先へ
        $request->session()->regenerate();

        return redirect()->intended(route('onetime.show'));
    }


    // ===============================================
    // ★★★ メール認証機能 (EmailVerificationControllerから移行) ★★★
    // ===============================================

    /**
     * メール認証通知ページを表示します。
     */
    public function notice()
    {
        return view('auth.verify-email');
    }


    /**
     * メール認証リクエストを処理します。
     */
    public function verify(EmailVerificationRequest $request)
    {
        // ユーザーのメールがすでに確認済みかチェック
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('profile_edit'));
        }

        // メールを検証済みとしてマーク
        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // 認証後のリダイレクト先を'profile_edit'ルートにし、`with('verified', true)`でメッセージを渡す
        return redirect()->intended(route('profile_edit'))->with('verified', true);
    }


    /**
     * メール認証通知を再送信します。
     */
    public function resend(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
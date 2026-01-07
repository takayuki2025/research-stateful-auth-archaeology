<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Kreait\Firebase\Auth as FirebaseAuth;
use App\Models\User; // Laravelのデフォルトユーザーモデルを使用

/**
 * Firebase IDトークンからユーザーを作成するためのカスタムプロバイダー。
 */
class FirebaseUserProvider implements UserProvider
{
    protected $firebaseAuth;

    public function __construct(FirebaseAuth $firebaseAuth)
    {
        // kreait/laravel-firebaseが提供するFirebase Authインスタンスを注入
        $this->firebaseAuth = $firebaseAuth;
    }

    /**
     * トークンを検証し、Firebaseユーザー情報に基づいてAuthenticatableユーザーを取得または作成します。
     *
     * @param array $credentials ここではFirebase IDトークンを含む連想配列を想定
     * @return Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        // 1. IDトークンが提供されているか確認
        if (!isset($credentials['token'])) {
            return null;
        }

        $idToken = $credentials['token'];

        try {
            // 2. Firebaseでトークンを検証 (ここでAPIコールが発生)
            $verifiedIdToken = $this->firebaseProvider->verify($idToken); // ★ 必須（5〜30）

            // 3. 検証されたトークンからユーザーID (uid) を取得
            $uid = $verifiedIdToken->claims()->get('sub');

            // 4. Laravel側のDBでユーザーを検索
            $user = User::where('firebase_uid', $uid)->first();

            if (!$user) {
                // 5. ユーザーがLaravel DBに存在しない場合は新規作成
                $firebaseUser = $this->firebaseAuth->getUser($uid);

                $user = User::create([
                    'name' => $firebaseUser->displayName ?? 'Firebase User',
                    'email' => $firebaseUser->email,
                    'firebase_uid' => $uid, // FirebaseのUIDを保存
                    'password' => \Hash::make(\Str::random(24)), // パスワードは使用しないが必須のためランダム生成
                    'email_verified_at' => $firebaseUser->emailVerified ? now() : null,
                ]);
            }

            return $user;

        } catch (\Exception $e) {
            // トークンが無効、期限切れ、またはその他のエラーが発生した場合
            \Log::error('Firebase ID Token validation failed: ' . $e->getMessage());
            return null;
        }
    }

    // 以下のメソッドは、IDトークン認証フローでは使用しないため、デフォルト実装を維持します。
    public function retrieveById($identifier)
    {
        return null;
    }
    public function retrieveByToken($identifier, $token)
    {
        return null;
    }
    public function updateRememberToken(Authenticatable $user, $token)
    {
    }
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return false;
    }
}

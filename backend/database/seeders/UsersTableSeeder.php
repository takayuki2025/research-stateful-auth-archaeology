<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Kreait\Firebase\Contract\Auth;

class UsersTableSeeder extends Seeder
{
    protected $firebaseAuth;

    public function __construct(Auth $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    public function run(): void
    {
        // ★ テストユーザー一覧（以前と同じ）
        $testUsers = [
            [
                'name' => 'テスト用のユーザ１',
                'email' => 'valid.email@example.com',
                'password' => 'testtest1',
                'post_number' => '111-1111',
                'address' => '東京都港区芝公園4-2-8',
                'building' => 'コーポA',
                'address_country' => 'JP',
                'shop_id' => null,
            ],
            [
                'name' => 'テスト用のユーザ2',
                'email' => 'taro.y@coachtech.com',
                'password' => 'testtest2',
                'post_number' => '222-2222',
                'address' => '大阪府大阪市阿倍野区阿倍野筋1-1-43',
                'building' => 'ハイツB',
                'address_country' => 'JP',
                'shop_id' => null,
            ],
            [
                'name' => 'テスト用のユーザ3',
                'email' => 'reina.n@coachtech.com',
                'password' => 'testtest3',
                'post_number' => '333-3333',
                'address' => '福岡県福岡市東区香椎照葉6-2-52',
                'building' => 'エトワールC',
                'address_country' => 'JP',
                'shop_id' => null,
            ],
            [
                'name' => 'テスト用のユーザ4',
                'email' => 'tomomi.a@coachtech.com',
                'password' => 'testtest4',
                'post_number' => '444-4444',
                'address' => '北海道札幌市中央区北五条西2丁目',
                'building' => 'エスポワールD',
                'address_country' => 'JP',
                'shop_id' => null,
            ],
            [
                'name' => '川田隆之',
                'email' => 't.principle.k2024@gmail.com',
                'password' => 'takayuki',
                'post_number' => '326-0000',
                'address' => '栃木県',
                'building' => '足利 FacilityCenter',
                'address_country' => 'JP',
                'shop_id' => null,
            ],
        ];

        // ★ 既存ユーザー削除
        $emailsToCleanup = array_column($testUsers, 'email');
        User::whereIn('email', $emailsToCleanup)->delete();

        foreach ($testUsers as $userData) {
            $email = $userData['email'];
            $password = $userData['password'];

            try {
                // Firebase の削除 → 作成
                try {
                    $record = $this->firebaseAuth->getUserByEmail($email);
                    $this->firebaseAuth->deleteUser($record->uid);
                } catch (\Exception $e) {
                }

                $firebaseUser = $this->firebaseAuth->createUser([
                    'email' => $email,
                    'emailVerified' => true,
                    'password' => $password,
                    'displayName' => $userData['name'],
                ]);

                // ★ Laravel DB へ保存（ロール付与なし）
                User::updateOrCreate(
                    ['firebase_uid' => $firebaseUser->uid],
                    [
                        'name' => $userData['name'],
                        'email' => $email,
                        'password' => Hash::make(Str::random(16)),
                        'post_number' => $userData['post_number'],
                        'address' => $userData['address'],
                        'building' => $userData['building'],
                        'address_country' => $userData['address_country'],
                        'shop_id' => $userData['shop_id'],
                        'email_verified_at' => now(),
                        'first_login_at' => now(),
                    ]
                );

                Log::info("User synced: $email");

            } catch (\Exception $e) {
                Log::error("Failed to sync user ($email): " . $e->getMessage());
                throw $e;
            }
        }
    }
}

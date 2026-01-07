<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\UserAddress;

class UserAddressesTableSeeder extends Seeder
{
    public function run(): void
    {
        // 対象ユーザー（UsersTableSeeder と同一）
        $emails = [
            'valid.email@example.com',
            'taro.y@coachtech.com',
            'reina.n@coachtech.com',
            'tomomi.a@coachtech.com',
            't.principle.k2024@gmail.com',
        ];

        $users = User::whereIn('email', $emails)->get();

        foreach ($users as $user) {
            try {
                // 既存 primary を削除（再実行安全）
                UserAddress::where('user_id', $user->id)
                    ->where('is_primary', true)
                    ->delete();

                UserAddress::create([
                    'user_id'        => $user->id,
                    'post_number'    => $user->post_number ?? '000-0000',
                    'prefecture'     => $this->guessPrefecture($user->address),
                    'city'           => $this->guessCity($user->address),
                    'address_line1'  => $user->address ?? '住所未登録',
                    'address_line2'  => $user->building,
                    'recipient_name' => $user->name,
                    'phone'          => null,
                    'is_primary'     => true,
                ]);

                Log::info("Primary address created for user_id={$user->id}");

            } catch (\Throwable $e) {
                Log::error(
                    "Failed to seed address for user_id={$user->id}: " . $e->getMessage()
                );
                throw $e;
            }
        }
    }

    /**
     * かなり簡易だが Seeder 用としては十分
     */
    private function guessPrefecture(?string $address): string
    {
        if (!$address) {
            return '未設定';
        }

        if (str_contains($address, '東京')) {
            return '東京都';
        }
        if (str_contains($address, '千葉')) {
            return '千葉県';
        }
        if (str_contains($address, '静岡')) {
            return '静岡県';
        }
        if (str_contains($address, '長野')) {
            return '長野県';
        }
        if (str_contains($address, '栃木')) {
            return '栃木県';
        }

        return '未設定';
    }

    private function guessCity(?string $address): string
    {
        // 今回は Seeder 用なので簡易でOK
        return '未設定';
    }
}

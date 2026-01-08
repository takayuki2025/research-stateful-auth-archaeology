<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            /* ============================================================
               🔐 Firebase & Laravel Auth 統合
            ============================================================ */
            $table->string('firebase_uid')->unique()->nullable()
                ->comment('Firebase UID（Firebaseログイン時に必須）');

            /* ============================================================
               🏪 マルチテナント
            ============================================================ */
            $table->foreignId('shop_id')
                ->nullable()
                ->comment('所属店舗。null の場合はフリマ利用者');

            /* ============================================================
               👤 認証・識別情報
            ============================================================ */
            $table->string('name', 255);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

            $table->timestamp('first_login_at')->nullable()
                ->comment('初回ログイン完了時刻（オンボーディング制御用）');

            // 🔥 プロフィール完了フラグ（最終形）
            $table->boolean('profile_completed')
                ->default(false)
                ->comment('プロフィール（配送先等）完了フラグ');

            /* ============================================================
               🔐 Laravel Auth
            ============================================================ */
            $table->string('password')->nullable();

            /* ============================================================
               ⚠️ 旧：住所系（将来 Profile テーブルへ完全移行予定）
            ============================================================ */
            $table->string('post_number')->nullable();
            $table->string('address')->nullable();
            $table->string('building')->nullable();
            $table->string('address_country')->nullable();

            /* ============================================================
               🖼 プロフィール画像
            ============================================================ */
            $table->string('user_image')->nullable();

            /* ============================================================
               🔐 Laravel 標準
            ============================================================ */
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
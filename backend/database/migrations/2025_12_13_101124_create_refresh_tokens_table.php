<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // ★ 生トークンは保存せず、ハッシュのみ保存
            $table->string('token_hash', 64)->unique()
                ->comment('SHA-256 などでハッシュ化されたトークン');

            $table->boolean('revoked')->default(false);

            $table->timestamp('expires_at');

            // デバイス単位での管理
            $table->string('device_id', 64)->nullable()
                ->comment('フロント側で生成した一意なデバイスID');
            $table->string('device_name', 255)->nullable()
                ->comment('ユーザーに見せるデバイス名（例：iPhone 15 Chrome）');

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
